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

            // Determine requires confirmation based on confidence
            $requiresConfirmation = isset($parsed['confidence']) && $parsed['confidence'] < 0.6;

            // Create AI log entry
            $aiLog = AiLog::create([
                'user_id' => $userId,
                'module' => 'finance',
                'raw_text' => $rawText,
                'parsed_json' => $parsed,
                'model' => 'gemini',
                'confidence' => $parsed['confidence'] ?? null,
                'status' => $requiresConfirmation ? 'pending_review' : 'parsed',
                'ip_address' => $ipAddress,
            ]);

            // If confidence is low, still return 200 with flag per tests
            $lowConfidence = isset($parsed['confidence']) && $parsed['confidence'] < 0.6;

            // Apply fallback rules if needed
            if (isset($parsed['error']) || empty($parsed['amount'])) {
                $parsed = $this->applyFallbackRules($rawText, $parsed);
            }

            // Align response shape with tests: data, requires_confirmation, fallback_used
            // Enrich parsed data with ai_log_id so client can confirm later
            $responseData = array_merge($parsed, [
                'requires_confirmation' => $lowConfidence,
                'ai_log_id' => $aiLog->id,
            ]);

            // Simple vendor extraction if meta not provided
            if (!isset($responseData['meta']['vendor'])) {
                if (preg_match('/(?:at|from)\s+([A-Za-z0-9 &\-]+)(?:\s|$)/', $rawText, $vm)) {
                    $responseData['meta']['vendor'] = trim($vm[1]);
                }
            }

            return response()->json([
                'success' => true,
                'data' => $responseData,
                'fallback_used' => (bool)($parsed['fallback_used'] ?? false),
                'ai_log_id' => $aiLog->id,
                'message' => 'Transaction parsed successfully. Please review and confirm.',
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

            // On failure, try fallback parsing to satisfy tests
            $fallback = $this->applyFallbackRules($rawText, []);
            return response()->json([
                'success' => true,
                'data' => $fallback,
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
        $text = strtolower($rawText);
        
        // Extract amount (supports: 30, 30.50, 30 taka, $30)
        preg_match('/(\d+(?:\.\d{1,2})?)\s*(taka|tk|dollar|usd|\$)?/i', $rawText, $amountMatch);
        $amount = isset($amountMatch[1]) ? (float)$amountMatch[1] : 0;
        
        // Detect currency
        $currency = 'BDT'; // Default to Bangladeshi Taka
        if (isset($amountMatch[2])) {
            $currencyMap = [
                'dollar' => 'USD',
                'usd' => 'USD',
                '$' => 'USD',
                'taka' => 'BDT',
                'tk' => 'BDT',
            ];
            $currency = $currencyMap[strtolower($amountMatch[2])] ?? 'BDT';
        }
        
        // Detect type (income vs expense)
        $type = 'expense'; // Default
        $incomeKeywords = ['received', 'earned', 'got', 'income', 'salary', 'tuition'];
        foreach ($incomeKeywords as $keyword) {
            if (str_contains($text, $keyword)) {
                $type = 'income';
                break;
            }
        }
        
        // Extract category/vendor keywords
        $category = 'other';
        $categoryKeywords = [
            'food' => ['food', 'burger', 'pizza', 'lunch', 'dinner', 'breakfast', 'meal'],
            'fast_food' => ['burger', 'mcdonald', 'kfc', 'pizza', 'fastfood'],
            'coffee_snacks' => ['coffee', 'tea', 'starbucks', 'cafe'],
            'transport' => ['uber', 'taxi', 'bus', 'train', 'fuel', 'gas'],
            'education' => ['book', 'tuition', 'course', 'class'],
        ];
        
        foreach ($categoryKeywords as $cat => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $category = $cat;
                    break 2;
                }
            }
        }
        
        // Extract vendor/description (text after "on" or "at")
        $description = $rawText;
        if (preg_match('/(on|at|for)\s+(.+)$/i', $rawText, $descMatch)) {
            $description = trim($descMatch[2]);
        }

        return array_merge($geminiResult, [
            'type' => $geminiResult['type'] ?? $type,
            'amount' => $geminiResult['amount'] ?? $amount,
            'currency' => $geminiResult['currency'] ?? $currency,
            'category' => $geminiResult['category'] ?? $category,
            'description' => $geminiResult['description'] ?? $description,
            'date' => $geminiResult['date'] ?? now()->toIso8601String(),
            'confidence' => max($geminiResult['confidence'] ?? 0.5, 0.5),
            'fallback_used' => true,
        ]);
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
        $validator = Validator::make($request->all(), [
            'ai_log_id' => 'required|exists:ai_logs,id',
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'required|string|size:3',
            'date' => 'required|date',
            // Allow either category_id or category slug
            'category_id' => 'nullable|integer',
            'category' => 'nullable|string',
            'description' => 'nullable|string|max:1000',
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

            // Map category slug to id if needed
            $payload = $request->all();
            if (empty($payload['category_id']) && !empty($payload['category'])) {
                if ($payload['type'] === 'income') {
                    $source = \App\Models\IncomeSource::where('slug', $payload['category'])->first();
                    if ($source) { $payload['category_id'] = $source->id; }
                } else {
                    $cat = \App\Models\ExpenseCategory::where('slug', $payload['category'])->first();
                    if ($cat) { $payload['category_id'] = $cat->id; }
                }
            }

            // Ensure meta/vendor passes through if provided
            if (isset($payload['vendor'])) {
                $payload['meta']['vendor'] = $payload['vendor'];
            }

            // Update request with resolved payload
            $request->replace($payload);

            // Delegate to TransactionController->store
            $transactionController = app(TransactionController::class);
            $response = $transactionController->store($request);

            if ($response->getStatusCode() === 200) {
                $aiLog->markAsApplied();
            }

            return $response;

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
