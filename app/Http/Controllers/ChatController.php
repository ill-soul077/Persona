<?php

namespace App\Http\Controllers;

use App\Models\AiLog;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * Chat Controller
 * 
 * Handles AI-powered natural language parsing for finance and task inputs.
 * Uses Gemini API to extract structured data from user messages.
 */
class ChatController extends Controller
{
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Parse finance text using Gemini AI
     * 
     * Accepts natural language input like:
     * - "spent 30 taka on burger"
     * - "received 5000 taka tuition"
     * - "paid 150 for coffee at Starbucks downtown"
     * 
     * Returns structured JSON with extracted fields for user confirmation
     */
    public function parseFinance(Request $request)
    {
        // Ensure API returns 401 for unauthenticated requests (not redirect)
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.'
            ], 401);
        }

        // Simple rate limiting: max 60 requests per user per minute
        $userId = Auth::id();
        $rateKey = 'chat:parse_finance:' . $userId . ':' . now()->format('YmdHi');
        $count = cache()->increment($rateKey, 1);
        if ($count === 1) {
            cache()->put($rateKey, 1, now()->addMinute());
        }
        if ($count > 60) {
            return response()->json([
                'success' => false,
                'message' => 'Too Many Requests'
            ], 429);
        }

        $validator = Validator::make($request->all(), [
            'text' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $rawText = $request->text;
        $userId = Auth::id();
        $ipAddress = $request->ip();

        try {
            // Call Gemini service to parse the text
            $parsed = $this->geminiService->parseFinanceText($rawText);

            // Normalize to transactions array
            $transactions = [];
            if (isset($parsed['transactions']) && is_array($parsed['transactions'])) {
                $transactions = $parsed['transactions'];
            } else {
                // backward compatible single object
                $transactions = [ [
                    'type' => $parsed['type'] ?? 'expense',
                    'amount' => $parsed['amount'] ?? 0,
                    'currency' => $parsed['currency'] ?? 'BDT',
                    'category' => $parsed['category'] ?? 'other',
                    'description' => $parsed['description'] ?? $rawText,
                    'meta' => $parsed['meta'] ?? [],
                    'date' => $parsed['date'] ?? now()->toIso8601String(),
                    'confidence' => $parsed['confidence'] ?? 0.5,
                ] ];
            }

            // Determine requires confirmation if any has low confidence
            $requiresConfirmation = collect($transactions)->contains(function ($t) {
                return ($t['confidence'] ?? 0.0) < 0.6;
            });

            // Create AI log entry
            $aiLog = AiLog::create([
                'user_id' => $userId,
                'module' => 'finance',
                'raw_text' => $rawText,
                'parsed_json' => ['transactions' => $transactions],
                'model' => 'gemini',
                'confidence' => count($transactions) ? (collect($transactions)->avg('confidence')) : null,
                'status' => $requiresConfirmation ? 'pending_review' : 'parsed',
                'ip_address' => $ipAddress,
            ]);

            // If confidence is low, still return 200 with flag per tests
            $lowConfidence = isset($parsed['confidence']) && $parsed['confidence'] < 0.6;

            // Apply fallback rules if needed
            // If AI indicated failure, use regex fallback to split multiple
            if (isset($parsed['error'])) {
                $fallback = $this->applyFallbackRules($rawText, []);
                if (isset($fallback['transactions'])) {
                    $transactions = $fallback['transactions'];
                } else {
                    $transactions = [ $fallback ];
                }
            }

            // Enrich each with ai_log_id for confirm
            $transactions = array_map(function ($t) use ($aiLog) {
                $t['ai_log_id'] = $aiLog->id;
                return $t;
            }, $transactions);

            return response()->json([
                'success' => true,
                'data' => [
                    'transactions' => $transactions,
                    'requires_confirmation' => $requiresConfirmation,
                    'ai_log_id' => $aiLog->id,
                ],
                'fallback_used' => (bool)($parsed['fallback_used'] ?? false),
                'ai_log_id' => $aiLog->id,
                'message' => 'Parsed successfully. Please review and confirm.',
            ]);

        } catch (\Exception $e) {
            Log::error('Finance parse error', [
                'user_id' => $userId,
                'raw_text' => $rawText,
                'error' => $e->getMessage()
            ]);

            // Create failed log
            AiLog::create([
                'user_id' => $userId,
                'module' => 'finance',
                'raw_text' => $rawText,
                'parsed_json' => null,
                'model' => 'gemini',
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'ip_address' => $ipAddress,
            ]);

            // On failure, try fallback parsing (multi supported)
            $fallback = $this->applyFallbackRules($rawText, []);
            $transactions = isset($fallback['transactions']) ? $fallback['transactions'] : [ $fallback ];
            return response()->json([
                'success' => true,
                'data' => [ 'transactions' => $transactions, 'requires_confirmation' => true, 'ai_log_id' => null ],
                'fallback_used' => true,
                'message' => 'Parsed using fallback rules due to AI error.',
            ], 200);
        }
    }

    /**
     * Apply fallback regex-based parsing rules
     * 
     * Used when Gemini fails or returns low confidence
     */
    protected function applyFallbackRules(string $rawText, array $geminiResult): array
    {
        // Multi-chunk fallback: split and parse each
        $chunks = preg_split('/(?<=[\.\!\?])\s+|\n+|\s+and\s+/i', $rawText);
        $items = [];
        foreach ($chunks as $chunk) {
            $chunk = trim($chunk);
            if ($chunk === '') continue;

            $text = strtolower($chunk);
            preg_match('/(\d+(?:\.\d{1,2})?)\s*(taka|tk|dollar|usd|\$)?/i', $chunk, $amountMatch);
            $amount = isset($amountMatch[1]) ? (float)$amountMatch[1] : 0;
            $currency = 'BDT';
            if (isset($amountMatch[2])) {
                $curTok = strtolower($amountMatch[2]);
                $currency = in_array($curTok, ['dollar','usd','$']) ? 'USD' : 'BDT';
            } else if (preg_match('/\b(usd|dollar|\$)\b/i', $chunk)) {
                $currency = 'USD';
            }

            $type = 'expense';
            foreach (['received','earned','got','income','salary','tuition'] as $kw) {
                if (str_contains($text, $kw)) { $type = 'income'; break; }
            }

            $category = 'other';
            $categoryKeywords = [
                'fast_food' => ['burger','pizza','kfc','mcdonald'],
                'coffee_snacks' => ['coffee','tea','cafe','starbucks'],
                'food' => ['food','lunch','dinner','breakfast','meal'],
                'clothing' => ['dress','shirt','clothes','jeans'],
                'transport' => ['uber','taxi','bus','train','fuel','gas'],
            ];
            foreach ($categoryKeywords as $cat => $keywords) {
                foreach ($keywords as $keyword) {
                    if (str_contains($text, $keyword)) { $category = $cat; break 2; }
                }
            }

            $description = $chunk;
            if (preg_match('/(on|at|for)\s+(.+)$/i', $chunk, $descMatch)) {
                $description = trim($descMatch[2]);
            } else {
                foreach (['burger','pizza','coffee','tea','dress','gift'] as $kw) {
                    if (str_contains($text, $kw)) { $description = $kw; break; }
                }
            }

            $items[] = [
                'type' => $type,
                'amount' => $amount,
                'currency' => $currency,
                'category' => $category,
                'description' => $description,
                'date' => now()->toDateString(),
                'meta' => [],
                'confidence' => $amount > 0 ? 0.6 : 0.3,
            ];
        }

        return [
            'transactions' => $items,
            'fallback_used' => true,
        ];
    }

    /**
     * Parse task text using Gemini AI
     */
    public function parseTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $rawText = $request->message;
        $userId = Auth::id();

        try {
            $parsed = $this->geminiService->parseTaskText($rawText);

            $aiLog = AiLog::create([
                'user_id' => $userId,
                'module' => 'tasks',
                'raw_text' => $rawText,
                'parsed_json' => $parsed,
                'model' => 'gemini',
                'confidence' => $parsed['confidence'] ?? null,
                'status' => 'pending_review',
                'ip_address' => $request->ip(),
            ]);

            return response()->json([
                'success' => true,
                'parsed' => $parsed,
                'ai_log_id' => $aiLog->id,
            ]);

        } catch (\Exception $e) {
            Log::error('Task parse error', [
                'user_id' => $userId,
                'raw_text' => $rawText,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to parse task.',
            ], 500);
        }
    }

    /**
     * Confirm and save parsed transaction
     */
    public function confirmTransaction(Request $request)
    {
        // Accept either a single object or an array of transactions
        $data = $request->all();
        $isBatch = isset($data['transactions']) && is_array($data['transactions']);

        $rulesSingle = [
            'ai_log_id' => 'required|exists:ai_logs,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'date' => 'required|date',
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
        ];

        if ($isBatch) {
            $validator = Validator::make($data, [
                'ai_log_id' => 'required|exists:ai_logs,id',
                'transactions' => 'required|array|min:1',
                'transactions.*.type' => 'required|in:income,expense',
                'transactions.*.amount' => 'required|numeric|min:0.01',
                'transactions.*.currency' => 'required|string|size:3',
                'transactions.*.date' => 'required|date',
                'transactions.*.category_id' => 'nullable|integer',
                'transactions.*.category' => 'nullable|string',
                'transactions.*.description' => 'nullable|string|max:1000',
            ]);
        } else {
            $validator = Validator::make($data, $rulesSingle);
        }

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $aiLog = AiLog::findOrFail($data['ai_log_id']);

            $saved = [];
            $toSave = $isBatch ? $data['transactions'] : [ $data ];
            $transactionController = app(TransactionController::class);

            foreach ($toSave as $idx => $payload) {
                // Map category slug to id if needed
                if (empty($payload['category_id']) && !empty($payload['category'])) {
                    if (($payload['type'] ?? 'expense') === 'income') {
                        $source = \App\Models\IncomeSource::where('slug', $payload['category'])->first();
                        if ($source) { $payload['category_id'] = $source->id; }
                    } else {
                        $cat = \App\Models\ExpenseCategory::where('slug', $payload['category'])->first();
                        if ($cat) { $payload['category_id'] = $cat->id; }
                    }
                }

                if (isset($payload['vendor'])) {
                    $payload['meta']['vendor'] = $payload['vendor'];
                }

                // Build Request instance per item and force JSON
                $itemReq = new Request($payload);
                $itemReq->headers->set('Accept', 'application/json');
                $itemReq->headers->set('Content-Type', 'application/json');
                $resp = $transactionController->store($itemReq);
                if (method_exists($resp, 'getStatusCode') && $resp->getStatusCode() === 200) {
                    $saved[] = json_decode($resp->getContent(), true)['transaction'] ?? null;
                } else {
                    // If any fails, continue and report partial success
                    Log::warning('Batch transaction item failed to save', ['index' => $idx]);
                }
            }

            if (count($saved) > 0) {
                $aiLog->markAsApplied();
                return response()->json([
                    'success' => true,
                    'message' => 'Transactions saved successfully',
                    'saved' => $saved,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No transactions were saved',
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Confirm and save parsed task
     */
    public function confirmTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ai_log_id' => 'required|exists:ai_logs,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'due_date' => 'nullable|date',
            'priority' => 'required|in:low,medium,high',
            'recurrence_type' => 'nullable|in:none,daily,weekly,monthly',
            'tags' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Mark AI log as applied
            $aiLog = AiLog::findOrFail($request->ai_log_id);
            
            // Parse tags
            $tags = null;
            if ($request->filled('tags')) {
                $tags = array_map('trim', explode(',', $request->tags));
            }

            // Create task
            $task = \App\Models\Task::create([
                'user_id' => Auth::id(),
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'priority' => $request->priority,
                'recurrence_type' => $request->recurrence_type ?? 'none',
                'recurrence_interval' => 1,
                'tags' => $tags,
                'status' => 'pending',
                'created_via_ai' => true,
                'ai_raw_input' => $aiLog->raw_text,
            ]);

            // Calculate next occurrence for recurring tasks
            if ($task->recurrence_type !== 'none' && $task->due_date) {
                $task->next_occurrence = $task->calculateNextOccurrence();
                $task->save();
            }

            // Log history
            $task->logHistory('created', [
                'title' => $task->title,
                'created_via_ai' => true,
            ]);

            $aiLog->markAsApplied();

            return response()->json([
                'success' => true,
                'message' => 'Task created successfully!',
                'task' => $task,
            ]);

        } catch (\Exception $e) {
            Log::error('Task confirmation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save task: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update existing task via AI parsing
     */
    public function updateTask(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:500',
            'task_id' => 'required|exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $rawText = $request->message;
        $userId = Auth::id();

        try {
            // Check task ownership
            $task = \App\Models\Task::findOrFail($request->task_id);
            if ($task->user_id !== $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized access.',
                ], 403);
            }

            // Parse the update text with context
            $contextPrompt = "Update task: '{$task->title}'. User says: '{$rawText}'";
            $parsed = $this->geminiService->parseTaskText($contextPrompt);

            // Create AI log
            $aiLog = AiLog::create([
                'user_id' => $userId,
                'module' => 'tasks',
                'raw_text' => $rawText,
                'parsed_json' => array_merge($parsed, ['task_id' => $task->id]),
                'model' => 'gemini',
                'confidence' => $parsed['confidence'] ?? null,
                'status' => 'pending_review',
                'ip_address' => $request->ip(),
            ]);

            // Detect action from text
            $action = $this->detectTaskAction($rawText);

            if ($action === 'complete') {
                $task->markAsCompleted();
                $aiLog->markAsApplied();

                return response()->json([
                    'success' => true,
                    'action' => 'completed',
                    'message' => 'Task marked as completed!',
                    'task' => $task->fresh(),
                ]);
            }

            return response()->json([
                'success' => true,
                'action' => 'update',
                'parsed' => $parsed,
                'current_task' => $task,
                'ai_log_id' => $aiLog->id,
                'message' => 'Update parsed. Please review and confirm.',
            ]);

        } catch (\Exception $e) {
            Log::error('Task update parse error', [
                'user_id' => $userId,
                'task_id' => $request->task_id,
                'raw_text' => $rawText,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to parse update.',
            ], 500);
        }
    }

    /**
     * Detect task action from raw text
     */
    protected function detectTaskAction(string $text): ?string
    {
        $text = strtolower($text);
        
        // Complete/done keywords
        if (preg_match('/\b(complete|completed|done|finished|mark as done)\b/i', $text)) {
            return 'complete';
        }

        // Uncomplete/reopen keywords
        if (preg_match('/\b(uncomplete|incomplete|reopen|not done|mark as pending)\b/i', $text)) {
            return 'uncomplete';
        }

        // Delete keywords
        if (preg_match('/\b(delete|remove|cancel)\b/i', $text)) {
            return 'delete';
        }

        return null;
    }
}
