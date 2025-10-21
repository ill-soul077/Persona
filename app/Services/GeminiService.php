<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Gemini Service
 * 
 * Handles all interactions with Google's Gemini API for natural language processing.
 * Parses user input from the chatbot into structured data for finance and task modules.
 * 
 * Features:
 * - Finance text parsing (income/expense transactions)
 * - Task text parsing (task creation from natural language)
 * - API health checking
 * - Response caching for performance
 * - Error handling and fallback mechanisms
 */
class GeminiService
{
    /**
     * Gemini API base URL
     */
    protected string $baseUrl;

    /**
     * Gemini API key
     */
    protected string $apiKey;

    /**
     * Model name
     */
    protected string $model;

    /**
     * Maximum tokens for response
     */
    protected int $maxTokens;

    /**
     * Temperature for response creativity (0.0 - 1.0)
     */
    protected float $temperature;

    /**
     * Cache TTL in seconds (24 hours)
     */
    protected int $cacheTtl = 86400;

    /**
     * In-memory cache for current request lifecycle to minimize repeated Cache facade hits.
     */
    private array $inMemoryCache = [];

    /**
     * Rate limiting: Max requests per minute
     */
    protected int $maxRequestsPerMinute = 50;

    /**
     * Circuit breaker: Max failures before breaking
     */
    protected int $circuitBreakerThreshold = 5;

    /**
     * Circuit breaker: Reset time in seconds
     */
    protected int $circuitBreakerResetTime = 300; // 5 minutes

    /**
     * Retry configuration
     */
    protected int $maxRetries = 3;
    protected int $retryBaseDelay = 1000; // milliseconds

    /**
     * Constructor - Initialize service with environment configuration
     */
    public function __construct()
    {
        // Prefer config/env but safely fallback to provided key
        $this->baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1'), '/');
        $this->apiKey = (string) config('services.gemini.api_key', env('GEMINI_API_KEY', 'AIzaSyD9LGmUfyUMp72oD88RGSyaDXsX1FDjbUc'));
        $this->model = (string) config('services.gemini.model', env('GEMINI_MODEL', 'gemini-2.5-flash'));
        $this->maxTokens = (int) config('services.gemini.max_tokens', env('GEMINI_MAX_TOKENS', 1024));
        $this->temperature = (float) config('services.gemini.temperature', env('GEMINI_TEMPERATURE', 0.5));

        Log::info('GeminiService initialized', [
            'default_model' => $this->model,
            'base_url' => $this->baseUrl
        ]);

        // Try to detect an available free model if not reachable; fallback sequence
        try {
            $detected = $this->findBestAvailableModel();
            if ($detected) {
                $this->model = $detected;
                Log::info('GeminiService detected available model', ['selected_model' => $this->model]);
            }
        } catch (\Throwable $e) {
            Log::warning('Gemini model discovery failed, using default', ['error' => $e->getMessage(), 'default_model' => $this->model]);
        }
    }    /**
     * Parse natural language finance input into structured data
     * 
     * @param string $rawText e.g., "spent 25 on coffee at Starbucks downtown"
     * @return array {
     *   'type': 'income|expense',
     *   'amount': float,
     *   'category': string (slug),
     *   'description': string,
     *   'meta': array (vendor, location, etc.),
     *   'confidence': float (0-1)
     * }
     * @throws \Exception on API failure
     */
    public function parseFinanceText(string $rawText, $userId = null): array
    {
        // Check cache first
        $cacheKey = $this->getCacheKey('finance', $rawText);
        // short-circuit from in-memory cache if available (keyed by cache key)
        if (isset($this->inMemoryCache[$cacheKey])) {
            return $this->inMemoryCache[$cacheKey];
        }

        $result = Cache::remember($cacheKey, $this->cacheTtl, function () use ($rawText) {
            try {
                $prompt = $this->buildFinancePrompt($rawText);
                $response = $this->callGeminiAPI($prompt);
                
                $parsed = $this->parseFinanceResponse($response, $rawText);
                // If low confidence, mark requires_confirmation
                if (isset($parsed['confidence']) && $parsed['confidence'] < 0.6) {
                    $parsed['requires_confirmation'] = true;
                }
                return $parsed;
            } catch (\Exception $e) {
                Log::error('Gemini Finance Parse Error', [
                    'raw_text' => $rawText,
                    'error' => $e->getMessage(),
                ]);
                
                return $this->getFinanceFallback($rawText, $e->getMessage());
            }
        });
        // store in in-memory cache for subsequent calls in the same request
        $this->inMemoryCache[$cacheKey] = $result;
        return $result;
    }

    /**
     * Parse natural language task input into structured task data
     * 
     * @param string $rawText e.g., "remind me to call mom tomorrow at 3pm"
     * @return array {
     *   'title': string,
     *   'description': string,
     *   'due_date': Carbon|null,
     *   'priority': 'low|medium|high',
     *   'recurrence': string|null,
     *   'confidence': float
     * }
     * @throws \Exception on API failure
     */
    public function parseTaskText(string $rawText): array
    {
        // Check cache first
        $cacheKey = $this->getCacheKey('tasks', $rawText);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($rawText) {
            try {
                $prompt = $this->buildTaskPrompt($rawText);
                $response = $this->callGeminiAPI($prompt);
                
                return $this->parseTaskResponse($response, $rawText);
            } catch (\Exception $e) {
                Log::error('Gemini Task Parse Error', [
                    'raw_text' => $rawText,
                    'error' => $e->getMessage(),
                ]);
                
                return $this->getTaskFallback($rawText, $e->getMessage());
            }
        });
    }

    /**
     * Health check for Gemini API connectivity
     * 
     * @return bool true if API is reachable and responding
     */
    public function healthCheck(): bool
    {
        try {
            $response = Http::timeout(5)
                ->get($this->baseUrl . '/models', [
                    'key' => $this->apiKey,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Gemini Health Check Failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Get API usage statistics
     * 
     * @return array Usage stats including requests, rate limits, circuit breaker status
     */
    public function getUsageStats(): array
    {
        $todayKey = 'gemini_requests_today';
        $rateKey = 'gemini_rate_limit:' . now()->format('Y-m-d_H:i');
        $circuitKey = 'gemini_circuit_breaker';

        $todayCount = Cache::get($todayKey, 0);
        $currentMinuteCount = Cache::get($rateKey, 0);
        $circuitBreaker = Cache::get($circuitKey, ['count' => 0, 'last_failure' => null]);

        return [
            'requests_today' => $todayCount,
            'requests_this_minute' => $currentMinuteCount,
            'rate_limit' => $this->maxRequestsPerMinute,
            'rate_limit_exceeded' => $currentMinuteCount >= $this->maxRequestsPerMinute,
            'circuit_breaker' => [
                'status' => $this->isCircuitBreakerOpen() ? 'OPEN' : 'CLOSED',
                'failure_count' => $circuitBreaker['count'],
                'threshold' => $this->circuitBreakerThreshold,
                'last_failure' => $circuitBreaker['last_failure'],
                'reset_in_seconds' => $circuitBreaker['last_failure'] 
                    ? max(0, $this->circuitBreakerResetTime - now()->diffInSeconds($circuitBreaker['last_failure']))
                    : 0
            ],
            'retry_config' => [
                'max_retries' => $this->maxRetries,
                'base_delay_ms' => $this->retryBaseDelay
            ],
            'quota_remaining' => 1000 // Placeholder - would need Cloud Console integration
        ];
    }

    /**
     * Build prompt for finance text parsing
     */
        protected function buildFinancePrompt(string $rawText): string
    {
        $currentDate = now()->toIso8601String();
        
                return <<<PROMPT
You are a financial transaction parser. Extract one or more structured transactions from natural language input.

Input: "{$rawText}"

Return ONLY valid JSON (no markdown, no explanation, no code blocks) in this exact shape:
{
    "transactions": [
        {
            "type": "income" | "expense",
            "amount": number,
            "currency": "BDT" | "USD", // detect: taka/tk=BDT, dollar/$=USD, default BDT
            "category": string, // slug from list below
            "description": string, // brief item/reason
            "date": "{$currentDate}", // ISO 8601 (default current)
            "meta": { "vendor": string|null, "location": string|null, "tax": number|null, "tip": number|null },
            "confidence": number // 0.0 - 1.0
        }
    ]
}

CATEGORY SLUGS:
Income: from_home, tuition, freelance, part_time_job, investment, gift, other
Expense: food, fast_food, groceries, dining_out, coffee_snacks, clothing, education, books_supplies, tuition_fees, transport, fuel, public_transit, ride_sharing, entertainment, health, other

RULES:
- If multiple expenses/incomes appear separated by punctuation or conjunctions, output each as a separate object in transactions[]
- Type detection: "spent", "paid", "bought", "drank" → expense | "received", "earned", "got", "income" → income
- Amounts: support "30 taka", "5000 tk", "$25", "150" (numeric only in amount)
- Currency: "taka", "tk" → BDT | "dollar", "$", "usd" → USD | default → BDT
- Category mapping: "burger", "food" → fast_food | "tea", "coffee" → coffee_snacks | "tuition" → tuition (income) or tuition_fees (expense) | "dress", "shirt", "clothes" → clothing | "gift" → other
- Description: Extract item/reason from phrases like "on burger", "for coffee", etc.
- Vendor: "at Starbucks", "from McDonald's" → vendor name
- Confidence: 0.9+ if all fields clear, 0.7-0.8 if some ambiguity, <0.6 if unclear

EXAMPLES:
"spent 30 taka on burger" → {"transactions":[{"type":"expense","amount":30,"currency":"BDT","category":"fast_food","description":"burger","date":"{$currentDate}","meta":{},"confidence":0.95}]}
"received 5000 taka tuition" → {"transactions":[{"type":"income","amount":5000,"currency":"BDT","category":"tuition","description":"tuition payment","date":"{$currentDate}","meta":{},"confidence":0.92}]}
"brought dress 300 taka. drank tea 30 taka" → {"transactions":[{"type":"expense","amount":300,"currency":"BDT","category":"clothing","description":"dress","date":"{$currentDate}","meta":{},"confidence":0.9},{"type":"expense","amount":30,"currency":"BDT","category":"coffee_snacks","description":"tea","date":"{$currentDate}","meta":{},"confidence":0.95}]}

Return ONLY the JSON object.
PROMPT;
    }

    /**
     * Build prompt for task text parsing
     */
    protected function buildTaskPrompt(string $rawText): string
    {
        return <<<PROMPT
You are a task parser. Extract structured task data from natural language input.

Input: "{$rawText}"

Extract the following information and return ONLY valid JSON (no markdown, no explanation):
{
    "title": brief task title (max 50 chars),
    "description": detailed description,
    "due_date": ISO 8601 datetime or null (e.g., "2025-10-09 15:00:00"),
    "priority": "low", "medium", or "high",
    "recurrence": "daily", "weekly", "monthly", or null,
    "confidence": your confidence score (0.0 to 1.0)
}

Rules:
- Extract action verbs for title (e.g., "Call mom", "Submit assignment")
- Parse relative dates (tomorrow, next week, friday)
- Current datetime reference: {now()->toIso8601String()}
- Detect recurrence keywords (daily, weekly, every day, etc.)
- Infer priority from urgency words (urgent, asap → high)
PROMPT;
    }

    /**
     * Call Gemini API with prompt
     */
    protected function callGeminiAPI(string $prompt): array
    {
        // Circuit breaker & local rate limit (protect against spamming)
        if ($this->isCircuitBreakerOpen()) {
            throw new \Exception('Gemini API temporarily unavailable (circuit open). Please try again shortly.', 503);
        }
        if ($this->isRateLimitExceeded()) {
            throw new \Exception('Local rate limit exceeded for AI calls. Please wait a moment and retry.', 429);
        }

        $url = "{$this->baseUrl}/models/{$this->model}:generateContent?key={$this->apiKey}";

        // Wrap with retry/backoff to handle transient 429/5xx
        // Set HTTP timeout to 60s for Gemini 2.5 Flash (thinking mode takes longer)
        $response = $this->executeWithRetry(function () use ($url, $prompt) {
            $resp = Http::timeout(60)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => $this->temperature,
                        'maxOutputTokens' => $this->maxTokens,
                    ],
                ]);

            if (!$resp->successful()) {
                // Bubble up with HTTP status code for retry logic
                throw new \Exception('Gemini API request failed: ' . $resp->body(), $resp->status());
            }

            return $resp;
        });

        // Track quota usage locally
        $this->incrementRequestCounter();

        return $response->json();
    }

    /**
     * Attempt to find the best available free model.
     * Preference order: gemini-2.0-flash, gemini-1.5-flash, gemini-1.5-flash-8b
     */
    protected function findBestAvailableModel(): ?string
    {
        try {
            $resp = Http::timeout(8)->get($this->baseUrl . '/models', [ 'key' => $this->apiKey ]);
            if (!$resp->successful()) return null;
            $data = $resp->json();
            $models = collect($data['models'] ?? [])->pluck('name')->map(function ($n) {
                // names can be like models/gemini-2.0-flash
                return str_contains($n, '/') ? explode('/', $n)[1] : $n;
            });
            // Prefer newest/aliases first, then older fallbacks
            $prefs = [
                'gemini-2.5-flash',
                'gemini-flash-latest',
                'gemini-2.5-flash-lite',
                'gemini-2.0-flash',
                'gemini-1.5-flash',
                'gemini-1.5-flash-8b'
            ];
            foreach ($prefs as $m) {
                if ($models->contains($m)) return $m;
            }
            // last resort keep current
            return null;
        } catch (\Throwable $e) {
            Log::info('Model listing failed, skipping detection', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Generate budget guidance and summary from the provided context.
     * Note: Caching is now handled at the controller/DB level to reduce API calls.
     * @param array $context
     * @return array{summary:string, recommendations:array, suggestedAllocations:array, risks:array}
     */
    public function generateBudgetAdvice(array $context): array
    {
        try {
            Log::info('Generating budget advice', [
                'model' => $this->model,
                'month' => $context['month_name'] ?? 'unknown'
            ]);
            
            $prompt = $this->buildBudgetAdvicePrompt($context);
            $response = $this->callGeminiAPI($prompt);
            
            // Check if response was truncated due to MAX_TOKENS
            $finishReason = $response['candidates'][0]['finishReason'] ?? '';
            if ($finishReason === 'MAX_TOKENS') {
                Log::warning('Gemini response truncated due to MAX_TOKENS, using heuristic fallback', [
                    'model' => $this->model,
                    'thoughts_tokens' => $response['usageMetadata']['thoughtsTokenCount'] ?? 0
                ]);
                return $this->generateHeuristicBudgetAdvice($context);
            }
            
            // Log full response structure for debugging
            Log::debug('Gemini API full response', [
                'response_keys' => array_keys($response),
                'candidates_count' => count($response['candidates'] ?? []),
                'finish_reason' => $finishReason
            ]);
            
            $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Clean up markdown code blocks and any extra whitespace
            $text = preg_replace('/```json\s*|```/i', '', $text);
            $text = trim($text);
            
            // Log raw response for debugging
            Log::debug('Gemini budget advice raw response', [
                'length' => strlen($text),
                'preview' => substr($text, 0, 200)
            ]);
            
            $data = json_decode($text, true);
            if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
                Log::error('JSON decode failed', [
                    'error' => json_last_error_msg(),
                    'raw_text_preview' => substr($text, 0, 500)
                ]);
                throw new \RuntimeException('Invalid JSON from Gemini for budget advice: ' . json_last_error_msg());
            }
            
            Log::info('Budget advice generated successfully', ['model' => $this->model]);
            return $data;
        } catch (\Exception $e) {
            $errorMsg = strtolower($e->getMessage());
            
            // If quota exhausted, rate limited, or timed out, provide a graceful heuristic fallback
            if (str_contains($errorMsg, 'quota') || 
                str_contains($errorMsg, 'timeout') || 
                str_contains($errorMsg, 'timed out') ||
                str_contains($errorMsg, 'curl error 28') ||
                $e->getCode() === 429) {
                Log::warning('Using local fallback for budget advice due to API issue', [
                    'error' => substr($e->getMessage(), 0, 200),
                    'attempted_model' => $this->model
                ]);
                return $this->generateHeuristicBudgetAdvice($context);
            }
            throw $e;
        }
    }

    /**
     * Build the prompt for budget advice using provided context
     */
    protected function buildBudgetAdvicePrompt(array $ctx): string
    {
        $monthName = $ctx['month_name'] ?? now()->format('F Y');
        $budget = number_format((float)($ctx['budget_amount'] ?? 0), 2);
        $spent = number_format((float)($ctx['total_spent'] ?? 0), 2);
        $remaining = number_format((float)($ctx['remaining'] ?? 0), 2);
        $daysLeft = (int) ($ctx['days_left'] ?? 0);
        $currency = $ctx['currency'] ?? 'BDT';
        $categoryBreakdown = json_encode($ctx['category_breakdown'] ?? [], JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
You are a personal finance coach. Create a clear, actionable monthly budget summary and guidance.

Return ONLY valid JSON with this shape (no markdown, no commentary):
{
  "summary": string,                      // concise 2-3 sentences overview
  "recommendations": [                   // concrete, prioritized suggestions
    { "title": string, "detail": string }
  ],
  "suggestedAllocations": [              // how to spend remaining budget across categories
    { "category": string, "amount": number, "reason": string }
  ],
  "risks": [                             // pitfalls to avoid this month
    string
  ]
}

CONTEXT:
Month: {$monthName}
Currency: {$currency}
Budget amount: {$budget}
Total spent: {$spent}
Remaining: {$remaining}
Days left in month: {$daysLeft}
Category breakdown (spent so far): {$categoryBreakdown}

Guidelines:
- Be pragmatic and frugal; keep advice specific to the user's spending pattern.
- Suggested allocations should sum to <= Remaining and reflect typical needs (groceries, transport, bills, savings, discretionary).
- If over budget or near limit, focus on cost control and must-have categories only.
- Keep text short and scannable.
PROMPT;
    }

    /**
     * Heuristic fallback when Gemini quota/rate limit blocks the request.
     * Provides reasonable, deterministic guidance based on remaining/spent and common categories.
     * Public method so controller can call directly on timeout.
     */
    public function generateHeuristicBudgetAdvice(array $ctx): array
    {
        $remaining = (float) ($ctx['remaining'] ?? 0);
        $spent = (float) ($ctx['total_spent'] ?? 0);
        $budget = (float) ($ctx['budget_amount'] ?? 0);
        $daysLeft = max(1, (int) ($ctx['days_left'] ?? 1));
        $currency = $ctx['currency'] ?? 'BDT';
        $monthName = $ctx['month_name'] ?? now()->format('F Y');
        $perDay = $remaining / $daysLeft;

        $summary = $budget > 0
            ? sprintf('For %s, you have %s %.2f remaining out of %.2f (spent %.2f). That is ~%s %.2f per day for the next %d days.', $monthName, $currency, $remaining, $budget, $spent, $currency, $perDay, $daysLeft)
            : sprintf('For %s, no budget is set. You have spent %s %.2f so far.', $monthName, $currency, $spent);

        // Default allocation ratios (only if remaining > 0)
        $allocs = [];
        if ($remaining > 0) {
            $plan = [
                ['category' => 'groceries', 'ratio' => 0.30, 'reason' => 'Essential food and household items'],
                ['category' => 'transport', 'ratio' => 0.15, 'reason' => 'Commuting and necessary travel'],
                ['category' => 'bills', 'ratio' => 0.25, 'reason' => 'Utilities and mandatory payments'],
                ['category' => 'savings', 'ratio' => 0.20, 'reason' => 'Buffer for emergencies or goals'],
                ['category' => 'discretionary', 'ratio' => 0.10, 'reason' => 'Dining out, entertainment, small treats']
            ];
            foreach ($plan as $p) {
                $allocs[] = [
                    'category' => $p['category'],
                    'amount' => round($remaining * $p['ratio'], 2),
                    'reason' => $p['reason']
                ];
            }
            // Ensure sum <= remaining by trimming last bucket if rounding exceeded
            $sum = array_sum(array_column($allocs, 'amount'));
            if ($sum > $remaining) {
                $diff = $sum - $remaining;
                $allocs[count($allocs) - 1]['amount'] = max(0, $allocs[count($allocs) - 1]['amount'] - $diff);
            }
        }

        $overBudget = $budget > 0 && $spent > $budget;
        $nearLimit = $budget > 0 && !$overBudget && ($spent / $budget) >= 0.85;

        $recommendations = [];
        if ($overBudget) {
            $recommendations[] = [
                'title' => 'Cut discretionary spending',
                'detail' => 'Pause dining out and non-essential purchases until next month.'
            ];
            $recommendations[] = [
                'title' => 'Shift to essentials only',
                'detail' => 'Prioritize bills, groceries, and transport; keep daily spending under ' . $currency . ' ' . number_format($perDay, 2)
            ];
        } elseif ($nearLimit) {
            $recommendations[] = [
                'title' => 'Tighten daily cap',
                'detail' => 'Aim for no more than ' . $currency . ' ' . number_format($perDay, 2) . ' per day to stay within budget.'
            ];
            $recommendations[] = [
                'title' => 'Defer non-urgent buys',
                'detail' => 'Postpone clothing or entertainment purchases until next month.'
            ];
        } else {
            $recommendations[] = [
                'title' => 'Pre-allocate essentials',
                'detail' => 'Set aside funds for groceries, transport, and bills first; then savings.'
            ];
            $recommendations[] = [
                'title' => 'Automate a small saving',
                'detail' => 'Move 10–20% of remaining to savings to avoid end-of-month dips.'
            ];
        }

        $risks = $overBudget
            ? ['Overspending trend could impact upcoming bills; avoid credit if possible.']
            : ($nearLimit ? ['Near the budget limit—be cautious with discretionary categories.'] : ['Unexpected expenses (health, repairs) could arise—keep a buffer.']);

        return [
            'summary' => $summary,
            'recommendations' => $recommendations,
            'suggestedAllocations' => $allocs,
            'risks' => $risks,
            'fallback' => true
        ];
    }

    /**
     * Parse finance response from Gemini
     */
    protected function parseFinanceResponse(array $response, string $rawText): array
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean markdown code blocks if present
        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);
        
        $data = json_decode($text, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Gemini');
        }

        // Normalize to transactions[] shape
        if (isset($data['transactions']) && is_array($data['transactions'])) {
            // Ensure currency and date defaults per item
            foreach ($data['transactions'] as &$t) {
                $t['currency'] = $t['currency'] ?? 'BDT';
                // Normalize date to Y-m-d to pass before_or_equal:today rule
                $t['date'] = isset($t['date']) ? (\Carbon\Carbon::parse($t['date'])->toDateString()) : now()->toDateString();
                $t['meta'] = $t['meta'] ?? [];
                $t['confidence'] = (float)($t['confidence'] ?? ($t['amount'] ? 0.8 : 0.3));
            }
            return $data;
        }

        // Backward compatibility: single object → wrap
        if (isset($data['type']) && isset($data['amount'])) {
            $data['currency'] = $data['currency'] ?? 'BDT';
            $data['date'] = isset($data['date']) ? (\Carbon\Carbon::parse($data['date'])->toDateString()) : now()->toDateString();
            $data['meta'] = $data['meta'] ?? [];
            $single = $data;
            return [ 'transactions' => [ $single ] ];
        }

        // If nothing recognized, attempt simple split & parse locally
        return $this->getFinanceFallback($rawText, 'Invalid AI schema');
    }

    /**
     * Parse task response from Gemini
     */
    protected function parseTaskResponse(array $response, string $rawText): array
    {
        $text = $response['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        // Clean markdown code blocks if present
        $text = preg_replace('/```json\s*|\s*```/', '', $text);
        $text = trim($text);
        
        $data = json_decode($text, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response from Gemini');
        }

        return $data;
    }

    /**
     * Get fallback response for finance parsing failure
     */
    protected function getFinanceFallback(string $rawText, string $error): array
    {
        // Split input into sentences/clauses by period, newline, or ' and ' when amounts present
        $chunks = preg_split('/(?<=[\.\!\?])\s+|\n+|\s+and\s+/i', $rawText);
        $transactions = [];

        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') continue;

            $text = strtolower($chunk);
            // Extract amount if currency token exists for this chunk
            $amount = 0.0;
            $hasCurrencyToken = preg_match('/\$|usd|dollar|dollars|[৳]|bdt|taka|tk/i', $chunk) === 1;
            if ($hasCurrencyToken && preg_match('/(?:(?:\$|৳|tk)\s*)?([0-9]{1,3}(?:,[0-9]{3})*|[0-9]+)(?:\.([0-9]{1,2}))?/i', $chunk, $m)) {
                $intPart = str_replace(',', '', $m[1]);
                $decPart = isset($m[2]) ? ('.' . $m[2]) : '';
                $amount = (float)($intPart . $decPart);
            }

            // detect currency
            $currency = 'BDT';
            if (preg_match('/\$|\b(usd|dollar|dollars)\b/i', $chunk)) {
                $currency = 'USD';
            } elseif (preg_match('/[৳]|\b(bdt|taka|tk)\b/i', $chunk)) {
                $currency = 'BDT';
            }

            // detect type
            $type = preg_match('/\b(received|earned|got|income|salary)\b/i', $text) ? 'income' : 'expense';

            // category mapping including clothing, gift
            $category = 'other';
            $map = [
                'groceries' => ['grocery', 'groceries', 'supermarket'],
                'transport' => ['uber', 'ride', 'taxi', 'bus', 'train', 'transport'],
                'entertainment' => ['movie', 'netflix', 'subscription', 'cinema'],
                'health' => ['doctor', 'hospital', 'medicine', 'health'],
                'utilities' => ['electricity', 'water bill', 'gas bill', 'utility'],
                'fast_food' => ['burger', 'pizza', 'kfc', 'mcdonald'],
                'coffee_snacks' => ['coffee', 'tea', 'cafe', 'starbucks'],
                'clothing' => ['dress', 'shirt', 'clothes', 'jeans', 't-shirt'],
            ];
            foreach ($map as $cat => $terms) {
                foreach ($terms as $term) {
                    if (str_contains($text, $term)) { $category = $cat; break 2; }
                }
            }

            // description heuristics: after 'on/for' else first noun-ish word
            $description = $chunk;
            if (preg_match('/(?:on|for)\s+([^\.]+)$/i', $chunk, $dm)) {
                $description = trim($dm[1]);
            } else {
                // Try common items
                foreach (['burger','pizza','coffee','tea','dress','shirt','gift'] as $kw) {
                    if (str_contains($text, $kw)) { $description = $kw; break; }
                }
            }

            $transactions[] = [
                'type' => $type,
                'amount' => $amount,
                'currency' => $currency,
                'category' => $category,
                'description' => $description,
                'meta' => [],
                'date' => now()->toDateString(),
                'confidence' => $amount > 0 ? 0.7 : 0.0,
            ];
        }

        return [
            'transactions' => $transactions,
            'error' => $error,
            'fallback_used' => true,
        ];
    }

    /**
     * Get fallback response for task parsing failure
     */
    protected function getTaskFallback(string $rawText, string $error): array
    {
        return [
            'title' => substr($rawText, 0, 50),
            'description' => $rawText,
            'due_date' => null,
            'priority' => 'medium',
            'recurrence' => null,
            'confidence' => 0.0,
            'error' => $error,
        ];
    }

    /**
     * Generate cache key for parsed results
     */
    protected function getCacheKey(string $module, string $rawText): string
    {
        return sprintf('gemini:parse:%s:%s', $module, md5($rawText));
    }

    /**
     * Increment request counter for usage tracking
     */
    protected function incrementRequestCounter(): void
    {
        $key = 'gemini_requests_today';
        $count = Cache::get($key, 0);
        Cache::put($key, $count + 1, now()->endOfDay());
    }

    /**
     * Check if rate limit is exceeded
     */
    protected function isRateLimitExceeded(): bool
    {
        $key = 'gemini_rate_limit:' . now()->format('Y-m-d_H:i');
        $count = Cache::get($key, 0);
        
        if ($count >= $this->maxRequestsPerMinute) {
            Log::warning('Gemini API rate limit exceeded', [
                'count' => $count,
                'limit' => $this->maxRequestsPerMinute,
                'minute' => now()->format('Y-m-d H:i')
            ]);
            return true;
        }
        
        Cache::put($key, $count + 1, 120); // 2 minutes TTL
        return false;
    }

    /**
     * Check circuit breaker status
     */
    protected function isCircuitBreakerOpen(): bool
    {
        $key = 'gemini_circuit_breaker';
        $failures = Cache::get($key, ['count' => 0, 'last_failure' => null]);
        
        // Reset if enough time has passed
        if ($failures['last_failure'] && now()->diffInSeconds($failures['last_failure']) > $this->circuitBreakerResetTime) {
            Cache::forget($key);
            return false;
        }
        
        if ($failures['count'] >= $this->circuitBreakerThreshold) {
            Log::error('Gemini API circuit breaker is OPEN', [
                'failures' => $failures['count'],
                'threshold' => $this->circuitBreakerThreshold,
                'last_failure' => $failures['last_failure']
            ]);
            return true;
        }
        
        return false;
    }

    /**
     * Record circuit breaker failure
     */
    protected function recordCircuitBreakerFailure(): void
    {
        $key = 'gemini_circuit_breaker';
        $failures = Cache::get($key, ['count' => 0, 'last_failure' => null]);
        
        Cache::put($key, [
            'count' => $failures['count'] + 1,
            'last_failure' => now()
        ], $this->circuitBreakerResetTime);
    }

    /**
     * Reset circuit breaker on success
     */
    protected function resetCircuitBreaker(): void
    {
        Cache::forget('gemini_circuit_breaker');
    }

    /**
     * Execute API call with retry logic and exponential backoff.
     * Enhanced for 429 errors but keeps total wait time under 30s to prevent PHP timeouts.
     */
    protected function executeWithRetry(callable $apiCall, int $attempt = 1)
    {
        try {
            $response = $apiCall();
            
            // Success - reset circuit breaker
            $this->resetCircuitBreaker();
            
            return $response;
            
        } catch (\Exception $e) {
            $statusCode = method_exists($e, 'getCode') ? $e->getCode() : 500;
            
            // For 429 (rate limit), use 3 retries with shorter delays to avoid PHP timeout
            // For other errors, use 2 retries
            $maxRetries = $statusCode === 429 ? 3 : 2;
            $shouldRetry = in_array($statusCode, [429, 500, 503, 504]) && $attempt < $maxRetries;
            
            if ($shouldRetry) {
                // Calculate exponential backoff with jitter
                // Keep delays short: 2s, 4s, 8s max to stay under 60s timeout
                $baseDelay = $statusCode === 429 
                    ? 2000 * pow(2, $attempt - 1)  // 2s, 4s, 8s for 429
                    : 1000 * pow(2, $attempt - 1); // 1s, 2s for others
                    
                $jitter = rand(0, (int)($baseDelay * 0.3)); // 30% jitter
                $delay = ($baseDelay + $jitter) / 1000; // Convert to seconds
                
                // Cap individual delay at 10s to prevent runaway waits
                $delay = min($delay, 10);
                
                Log::warning('Retrying Gemini API call', [
                    'attempt' => $attempt,
                    'max_retries' => $maxRetries,
                    'status_code' => $statusCode,
                    'delay_seconds' => round($delay, 2),
                    'error_preview' => substr($e->getMessage(), 0, 150)
                ]);
                
                sleep((int)ceil($delay));
                
                return $this->executeWithRetry($apiCall, $attempt + 1);
            }
            
            // Max retries exceeded or non-retryable error
            $this->recordCircuitBreakerFailure();
            
            Log::error('Gemini API call failed after retries', [
                'attempts' => $attempt,
                'status_code' => $statusCode,
                'error_preview' => substr($e->getMessage(), 0, 300)
            ]);
            
            throw $e;
        }
    }

    /**
     * Scan receipt image using Gemini Vision API
     * 
     * @param string $imageBase64 Base64 encoded image data
     * @param string $mimeType Image MIME type (e.g., 'image/jpeg', 'image/png')
     * @return array {
     *   'amount': float,
     *   'date': string (ISO format),
     *   'description': string,
     *   'merchantName': string,
     *   'category': string
     * }
     * @throws \Exception on API failure
     */
    public function scanReceipt(string $imageBase64, string $mimeType = 'image/jpeg'): array
    {
        try {
            // Check circuit breaker
            if ($this->isCircuitBreakerOpen()) {
                return [
                    'success' => false,
                    'error' => 'Service temporarily unavailable. Please try again in a few minutes.'
                ];
            }

            // Check rate limit
            if ($this->isRateLimitExceeded()) {
                return [
                    'success' => false,
                    'error' => 'Rate limit exceeded. Please wait a moment and try again.'
                ];
            }

            // Check cache for duplicate uploads (using image hash)
            $imageHash = md5($imageBase64);
            $cacheKey = "receipt_scan:{$imageHash}";
            
            if (Cache::has($cacheKey)) {
                Log::info('Returning cached receipt scan result', ['hash' => $imageHash]);
                return Cache::get($cacheKey);
            }

            $prompt = "Analyze this receipt image and extract the following information in JSON format:\n" .
                      "- Total amount (just the number)\n" .
                      "- Date (in ISO format)\n" .
                      "- Description or items purchased (brief summary)\n" .
                      "- Merchant/store name\n" .
                      "- Suggested category (one of: housing,transportation,groceries,utilities,entertainment,food,shopping,healthcare,education,personal,travel,insurance,gifts,bills,other-expense )\n\n" .
                      "Only respond with valid JSON in this exact format:\n" .
                      "{\n" .
                      '  "amount": number,' . "\n" .
                      '  "date": "ISO date string",' . "\n" .
                      '  "description": "string",' . "\n" .
                      '  "merchantName": "string",' . "\n" .
                      '  "category": "string"' . "\n" .
                      "}\n\n" .
                      "If its not a receipt, return an empty object";

            // Use Gemini 2.0 Flash with v1 endpoint (v1beta not needed, multimodal supported)
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=AIzaSyCg2151c78yrL6aXmbEeeUJ4oKSHfn7QfA";

            // Log API request details
            Log::info('Starting receipt scan API call', [
                'url' => str_replace('key=', 'key=***', $url),
                'mime_type' => $mimeType,
                'image_size' => strlen($imageBase64) . ' bytes'
            ]);

            // Execute API call with retry logic
            $response = $this->executeWithRetry(function() use ($url, $prompt, $mimeType, $imageBase64) {
                $response = Http::timeout(30)->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                [
                                    'text' => $prompt
                                ],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $imageBase64
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.2,
                        'maxOutputTokens' => 1024,
                    ]
                ]);

                if (!$response->successful()) {
                    Log::error('Gemini receipt scan API error', [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    
                    // Create exception with status code for retry logic
                    $exception = new \Exception('Failed to scan receipt: ' . $response->body());
                    throw $exception;
                }

                return $response;
            });

            $data = $response->json();            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid response format from Gemini API');
            }

            $textResponse = $data['candidates'][0]['content']['parts'][0]['text'];
            
            // Extract JSON from response (remove markdown code blocks if present)
            $textResponse = preg_replace('/```json\s*/', '', $textResponse);
            $textResponse = preg_replace('/```\s*$/', '', $textResponse);
            $textResponse = trim($textResponse);
            
            $receiptData = json_decode($textResponse, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error('Failed to parse Gemini receipt response', [
                    'response' => $textResponse,
                    'error' => json_last_error_msg()
                ]);
                throw new \Exception('Failed to parse receipt data: ' . json_last_error_msg());
            }

            // Validate receipt data
            if (empty($receiptData)) {
                return [
                    'error' => 'Not a valid receipt image',
                    'success' => false
                ];
            }

            // Map category to match your system
            if (isset($receiptData['category'])) {
                $receiptData['category'] = $this->mapReceiptCategory($receiptData['category']);
            }

            $this->incrementRequestCounter();

            $result = array_merge([
                'success' => true,
                'amount' => $receiptData['amount'] ?? 0,
                'date' => $receiptData['date'] ?? now()->format('Y-m-d'),
                'description' => $receiptData['description'] ?? '',
                'merchantName' => $receiptData['merchantName'] ?? '',
                'category' => $receiptData['category'] ?? 'other-expense'
            ], $receiptData);

            // Cache the result for 1 hour (dedupe duplicate uploads)
            Cache::put($cacheKey, $result, 3600);

            return $result;

        } catch (\Exception $e) {
            Log::error('Receipt scanning error', [
                'error' => $e->getMessage(),
                'error_code' => $e->getCode(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            // Provide user-friendly error messages
            $userMessage = 'An error occurred while scanning the receipt';
            
            if (str_contains($e->getMessage(), 'models/') && str_contains($e->getMessage(), 'not found')) {
                $userMessage = 'The AI model is currently unavailable. Please try again later.';
            } elseif (str_contains($e->getMessage(), 'timeout')) {
                $userMessage = 'The request timed out. Please try again with a smaller image.';
            } elseif (str_contains($e->getMessage(), '429') || str_contains($e->getMessage(), 'quota')) {
                $userMessage = 'API quota exceeded. Please try again in a few minutes.';
            } elseif (str_contains($e->getMessage(), 'Failed to parse receipt data')) {
                $userMessage = 'Could not read the receipt. Please ensure the image is clear and try again.';
            }
            
            return [
                'success' => false,
                'error' => $userMessage,
                'debug_message' => config('app.debug') ? $e->getMessage() : null
            ];
        }
    }

    /**
     * Map receipt category to system category
     */
    protected function mapReceiptCategory(string $category): string
    {
        $categoryMap = [
            'housing' => 'housing',
            'transportation' => 'transportation',
            'groceries' => 'groceries',
            'utilities' => 'utilities',
            'entertainment' => 'entertainment',
            'food' => 'food',
            'shopping' => 'shopping',
            'healthcare' => 'healthcare',
            'education' => 'education',
            'personal' => 'personal',
            'travel' => 'travel',
            'insurance' => 'insurance',
            'gifts' => 'gifts',
            'bills' => 'bills',
            'other-expense' => 'other-expense'
        ];

        return $categoryMap[strtolower($category)] ?? 'other-expense';
    }

    /**
     * Manually reset circuit breaker (for admin/debugging)
     */
    public function resetCircuitBreakerManually(): void
    {
        $this->resetCircuitBreaker();
        Log::info('Circuit breaker manually reset');
    }

    /**
     * Clear all rate limit counters (for admin/debugging)
     */
    public function clearRateLimits(): void
    {
        $pattern = 'gemini_rate_limit:*';
        // Note: This is a simple implementation. In production, use Redis SCAN
        Cache::forget('gemini_requests_today');
        Log::info('Rate limits cleared');
    }
}
