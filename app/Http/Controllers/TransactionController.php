<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use App\Services\GeminiService;
use App\Models\AiLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Transaction Controller
 * 
 * Handles all transaction CRUD operations, filtering, and data exports.
 * Supports both manual entry and AI-assisted parsing.
 */
class TransactionController extends Controller
{
    use AuthorizesRequests;
    
    protected GeminiService $geminiService;

    public function __construct(GeminiService $geminiService)
    {
        $this->geminiService = $geminiService;
    }

    /**
     * Display dashboard with summary cards and charts
     */
    public function dashboard(Request $request)
    {
        $user = Auth::user();
    // Only apply date range if provided; otherwise include all records
    // Accept dates from query or headers (tests may pass as headers)
    $startDateInput = $request->input('start_date', $request->headers->get('start_date'));
    $endDateInput = $request->input('end_date', $request->headers->get('end_date'));
    $startDate = $startDateInput ? now()->parse($startDateInput) : now()->startOfMonth();
    $endDate = $endDateInput ? now()->parse($endDateInput) : now()->endOfMonth();

        // Summary calculations
        $incomeQuery = Transaction::income()->where('user_id', $user->id);
        if ($startDateInput || $endDateInput) {
            $incomeQuery = $incomeQuery->dateRange($startDate, $endDate);
        }
        $totalIncome = $incomeQuery->sum('amount');

        $expenseQuery = Transaction::expense()->where('user_id', $user->id);
        if ($startDateInput || $endDateInput) {
            $expenseQuery = $expenseQuery->dateRange($startDate, $endDate);
        }
        $totalExpense = $expenseQuery->sum('amount');

        $balance = $totalIncome - $totalExpense;

        // Recent transactions
        $recentTransactions = Transaction::with('category')
            ->where('user_id', $user->id)
            ->latest('date')
            ->limit(10)
            ->get();

        // Expense breakdown by category
        $breakdownQuery = Transaction::expense()
            ->where('user_id', $user->id);
        if ($startDateInput || $endDateInput) {
            $breakdownQuery = $breakdownQuery->dateRange($startDate, $endDate);
        }
        $expenseBreakdown = $breakdownQuery
            ->with('category')
            ->get()
            ->groupBy(fn($tx) => $tx->category?->name ?? 'Uncategorized')
            ->map(fn($group) => $group->sum('amount'))
            ->sortDesc();

        return view('finance.dashboard', compact(
            'totalIncome',
            'totalExpense',
            'balance',
            'recentTransactions',
            'expenseBreakdown',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display transactions list with filters
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['category', 'user'])
            ->where('user_id', Auth::id());

        // Apply filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('description', 'like', "%{$request->search}%")
                  ->orWhereJsonContains('meta->vendor', $request->search);
            });
        }

        $transactions = $query->latest('date')
            ->latest('created_at')
            ->paginate(20);

        // Get filter options
        $expenseCategories = ExpenseCategory::active()->parents()->with('children')->get();
        $incomeSources = IncomeSource::active()->get();

        return view('finance.transactions.index', compact(
            'transactions',
            'expenseCategories',
            'incomeSources'
        ));
    }

    /**
     * Show form for creating new transaction
     */
    public function create()
    {
        $expenseCategories = ExpenseCategory::active()->parents()->with('children')->get();
        $incomeSources = IncomeSource::active()->get();

        return view('finance.transactions.create', compact(
            'expenseCategories',
            'incomeSources'
        ));
    }

    /**
     * Store a newly created transaction
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01|max:9999999999.99',
            'currency' => 'required|string|size:3',
            'date' => 'required|date|before_or_equal:today',
            'category_id' => 'required|integer',
            'description' => 'nullable|string|max:1000',
            'meta.vendor' => 'nullable|string|max:255',
            'meta.location' => 'nullable|string|max:255',
            'meta.tax' => 'nullable|numeric|min:0',
            'meta.tip' => 'nullable|numeric|min:0',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120', // 5MB
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('receipts', 'public');
            }

            // Determine category type
            $categoryType = $request->type === 'income' 
                ? IncomeSource::class 
                : ExpenseCategory::class;

            // Build meta data
            $meta = array_filter([
                'vendor' => $request->input('meta.vendor'),
                'location' => $request->input('meta.location'),
                'tax' => $request->input('meta.tax'),
                'tip' => $request->input('meta.tip'),
                'attachment' => $attachmentPath,
            ]);

            $transaction = Transaction::create([
                'user_id' => Auth::id(),
                'type' => $request->type,
                'amount' => round($request->amount, 2),
                'currency' => strtoupper($request->currency),
                'date' => $request->date,
                'category_id' => $request->category_id,
                'category_type' => $categoryType,
                'description' => strip_tags($request->description),
                'meta' => empty($meta) ? null : $meta,
            ]);

            DB::commit();

            // Handle different request types
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaction created successfully!',
                    'transaction' => $transaction->load('category'),
                    'redirect' => route('finance.dashboard')
                ]);
            }

            return redirect()->route('finance.transactions.index')
                           ->with('success', 'Transaction created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create transaction: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                           ->withErrors(['error' => 'Failed to create transaction: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return view('finance.transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing transaction
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $expenseCategories = ExpenseCategory::active()->parents()->with('children')->get();
        $incomeSources = IncomeSource::active()->get();

        return view('finance.transactions.edit', compact(
            'transaction',
            'expenseCategories',
            'incomeSources'
        ));
    }

    /**
     * Update the specified transaction
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:income,expense',
            'amount' => 'required|numeric|min:0.01|max:9999999999.99',
            'currency' => 'required|string|size:3',
            'date' => 'required|date|before_or_equal:today',
            'category_id' => 'required|integer',
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $categoryType = $request->type === 'income' 
                ? IncomeSource::class 
                : ExpenseCategory::class;

            $transaction->update([
                'type' => $request->type,
                'amount' => round($request->amount, 2),
                'currency' => strtoupper($request->currency),
                'date' => $request->date,
                'category_id' => $request->category_id,
                'category_type' => $categoryType,
                'description' => strip_tags($request->description),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction updated successfully!',
                'transaction' => $transaction->fresh()->load('category')
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        try {
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => 'Transaction deleted successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete transaction: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get chart data for expense breakdown
     */
    public function chartData(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Expense breakdown by category - return flat mapping: { "Category": amount }
        $query = Transaction::expense()
            ->where('user_id', Auth::id());

        if ($startDate || $endDate) {
            $query = $query->dateRange($startDate ?? now()->startOfMonth(), $endDate ?? now()->endOfMonth());
        }

        $expenses = $query
            ->with('category')
            ->get();

        $breakdown = $expenses
            ->groupBy(fn($tx) => $tx->category?->name ?? 'Uncategorized')
            ->map(fn($group) => round($group->sum('amount'), 2));

        return response()->json($breakdown);
    }

    /**
     * Get transactions for a specific category (drill-down)
     */
    public function categoryDrilldown(Request $request)
    {
        // Accept category from query or headers (tests may pass as headers)
        $categoryName = $request->get('category', $request->headers->get('category'));
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = Transaction::expense()
            ->where('user_id', Auth::id())
            ->with('category');

        // Apply date range only if provided; otherwise include all records
        if ($startDate || $endDate) {
            $query = $query->dateRange($startDate ?? now()->startOfMonth(), $endDate ?? now()->endOfMonth());
        }

        $transactions = $query->get()
            ->filter(fn($tx) => ($tx->category?->name ?? 'Uncategorized') === $categoryName)
            ->values();

        return response()->json([
            'category' => $categoryName,
            'transactions' => $transactions,
            'total' => $transactions->sum('amount'),
            'count' => $transactions->count(),
        ]);
    }

    /**
     * Scan receipt image using AI
     */
    public function scanReceipt(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'receipt_image' => 'required|image|mimes:jpeg,jpg,png|max:5120' // 5MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'error' => $validator->errors()->first()
                ], 422);
            }

            $image = $request->file('receipt_image');
            $imageData = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();

            // Call Gemini API to scan receipt
            Log::info('Calling Gemini API for receipt scan', [
                'mime_type' => $mimeType,
                'image_size' => strlen($imageData)
            ]);
            
            $receiptData = $this->geminiService->scanReceipt($imageData, $mimeType);
            
            Log::info('Gemini API response received', [
                'success' => $receiptData['success'] ?? false,
                'has_error' => isset($receiptData['error']),
                'error' => $receiptData['error'] ?? null
            ]);

            if (!$receiptData['success']) {
                return response()->json([
                    'success' => false,
                    'error' => $receiptData['error'] ?? 'Failed to scan receipt',
                    'debug_message' => $receiptData['debug_message'] ?? null
                ], 400);
            }

            // Log AI usage
                // Log AI usage for auditing (reuses existing finance module enum)
                AiLog::create([
                    'user_id' => Auth::id(),
                    'module' => 'finance',
                    'raw_text' => json_encode($receiptData),
                    'parsed_json' => $receiptData,
                    'model' => 'gemini-2.0-flash',
                    'status' => 'parsed',
                    'ip_address' => $request->ip(),
                ]);

            return response()->json([
                'success' => true,
                'data' => $receiptData
            ]);

        } catch (\Exception $e) {
            Log::error('Receipt scan error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'error' => 'An error occurred while scanning the receipt'
            ], 500);
        }
    }
}
