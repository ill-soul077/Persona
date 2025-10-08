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
     * Constructor - Initialize service with environment configuration
     */
    public function __construct()
    {
        $this->baseUrl = config('services.gemini.base_url', env('GEMINI_BASE_URL'));
        $this->apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $this->model = config('services.gemini.model', env('GEMINI_MODEL', 'gemini-1.5-flash'));
        $this->maxTokens = (int) config('services.gemini.max_tokens', env('GEMINI_MAX_TOKENS', 1024));
        $this->temperature = (float) config('services.gemini.temperature', env('GEMINI_TEMPERATURE', 0.7));
    }

    /**
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
    public function parseFinanceText(string $rawText): array
    {
        // Check cache first
        $cacheKey = $this->getCacheKey('finance', $rawText);
        
        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($rawText) {
            try {
                $prompt = $this->buildFinancePrompt($rawText);
                $response = $this->callGeminiAPI($prompt);
                
                return $this->parseFinanceResponse($response, $rawText);
            } catch (\Exception $e) {
                Log::error('Gemini Finance Parse Error', [
                    'raw_text' => $rawText,
                    'error' => $e->getMessage(),
                ]);
                
                return $this->getFinanceFallback($rawText, $e->getMessage());
            }
        });
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
     * Get API usage statistics (placeholder for future implementation)
     * 
     * @return array {requests_today: int, quota_remaining: int}
     */
    public function getUsageStats(): array
    {
        // This would require tracking API calls in cache/database
        // For now, return mock data
        return [
            'requests_today' => Cache::get('gemini_requests_today', 0),
            'quota_remaining' => 1000, // Placeholder
        ];
    }

    /**
     * Build prompt for finance text parsing
     */
    protected function buildFinancePrompt(string $rawText): string
    {
        return <<<PROMPT
You are a financial transaction parser. Extract structured data from natural language input.

Input: "{$rawText}"

Extract the following information and return ONLY valid JSON (no markdown, no explanation):
{
    "type": "income" or "expense",
    "amount": numeric value,
    "category": category slug (food, fast_food, clothing, education, transport, entertainment, health, from_home, tuition, freelance, part_time_job, investment, gift, other),
    "description": brief description,
    "meta": {
        "vendor": vendor name if mentioned,
        "location": location if mentioned,
        "tax": tax amount if mentioned,
        "tip": tip amount if mentioned
    },
    "confidence": your confidence score (0.0 to 1.0)
}

Rules:
- If "spent", "paid", "bought" → type is "expense"
- If "received", "earned", "got" → type is "income"
- Extract numeric amounts (e.g., "15", "twenty five")
- Map to appropriate category slug
- Include vendor/location in meta if mentioned
- Return confidence based on clarity of input
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
        $response = Http::timeout(15)
            ->post("{$this->baseUrl}/models/{$this->model}:generateContent", [
                'key' => $this->apiKey,
            ], [
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

        if (!$response->successful()) {
            throw new \Exception('Gemini API request failed: ' . $response->body());
        }

        return $response->json();
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

        return $data;
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
        return [
            'type' => 'expense',
            'amount' => 0.00,
            'category' => 'other',
            'description' => $rawText,
            'meta' => [],
            'confidence' => 0.0,
            'error' => $error,
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
}
