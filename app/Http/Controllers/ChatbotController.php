<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use App\Services\GeminiService;
use App\Services\TransactionParserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ChatbotController extends Controller
{
    protected $geminiService;
    protected $transactionParser;

    public function __construct(GeminiService $geminiService, TransactionParserService $transactionParser)
    {
        $this->geminiService = $geminiService;
        $this->transactionParser = $transactionParser;
    }

    public function index()
    {
        return view('chatbot.index');
    }

    public function processMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide a valid message.'
            ], 400);
        }

        $message = $request->input('message');
        $user = Auth::user();

        try {
            // Check if the message looks like a transaction
            if ($this->transactionParser->isTransactionMessage($message)) {
                return $this->handleTransactionMessage($message, $user);
            } else {
                // Handle as general chat message
                return $this->handleGeneralMessage($message, $user);
            }

        } catch (\Exception $e) {
            Log::error('Chatbot processing error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Sorry, I encountered an error processing your message. Please try again.'
            ], 500);
        }
    }

    private function handleTransactionMessage(string $message, $user)
    {
        // Parse the transaction from natural language
        $parsed = $this->transactionParser->parseTransaction($message);

        // If we couldn't extract essential information, ask for clarification
        if (!$parsed['amount'] || $parsed['confidence'] < 0.5) {
            return response()->json([
                'success' => true,
                'type' => 'clarification',
                'message' => "I couldn't fully understand your transaction. Could you please specify the amount and what you spent on or earned? For example: 'spent 25 taka on coffee' or 'received 5000 salary'"
            ]);
        }

        // Return transaction preview for confirmation
        return response()->json([
            'success' => true,
            'type' => 'transaction_preview',
            'message' => $this->formatTransactionPreview($parsed),
            'transaction' => $parsed
        ]);
    }

    private function handleGeneralMessage(string $message, $user)
    {
        // Get user context for better AI responses
        $context = $this->getUserContext($user);
        
        // Use Gemini for general conversation
        $aiResponse = $this->geminiService->parseFinanceText($message, $user->id);

        if ($aiResponse) {
            return response()->json([
                'success' => true,
                'type' => 'message',
                'message' => $aiResponse['description'] ?? "I'm here to help with your finances and tasks. Try saying something like 'spent 50 on lunch' or ask me about your spending patterns."
            ]);
        }

        return response()->json([
            'success' => true,
            'type' => 'message',
            'message' => "I'm your personal finance assistant! I can help you track expenses and income. Try telling me about a transaction like 'spent 25 taka on coffee' or 'received 1000 from freelance work'."
        ]);
    }

    public function confirmTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'description' => 'required|string|max:255',
            'category' => 'nullable|array',
            'date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid transaction data provided.'
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = Auth::user();
            $data = $request->all();

            // Prepare transaction data
            $transactionData = [
                'user_id' => $user->id,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'description' => $data['description'],
                'date' => $data['date'],
                'currency' => 'BDT', // Default currency
            ];

            // Add category using polymorphic relationship
            if (isset($data['category']['id'])) {
                $transactionData['category_id'] = $data['category']['id'];
                if ($data['type'] === 'expense') {
                    $transactionData['category_type'] = 'App\Models\ExpenseCategory';
                } else {
                    $transactionData['category_type'] = 'App\Models\IncomeSource';
                }
            }

            // Create the transaction
            $transaction = Transaction::create($transactionData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaction added successfully! 💰',
                'transaction' => $transaction->load(['category', 'source'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction creation error: ' . $e->getMessage());
            Log::error('Transaction data: ' . json_encode($request->all()));

            return response()->json([
                'success' => false,
                'message' => 'Failed to save transaction. Error: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'data' => $request->all()
                ]
            ], 500);
        }
    }

    private function formatTransactionPreview(array $parsed): string
    {
        $type = ucfirst($parsed['type']);
        $amount = number_format($parsed['amount'], 2);
        $category = $parsed['category']['name'] ?? 'Uncategorized';
        
        return "📝 **Transaction Preview**\n\n" .
               "**Type:** {$type}\n" .
               "**Amount:** ৳{$amount}\n" .
               "**Category:** {$category}\n" .
               "**Description:** {$parsed['description']}\n\n" .
               "Would you like me to save this transaction?";
    }

    private function getUserContext($user): array
    {
        // Get recent transactions for context
        $recentTransactions = Transaction::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get(['amount', 'type', 'description', 'date'])
            ->toArray();

        // Get financial summary
        $totalIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'income')
            ->sum('amount');
        
        $totalExpenses = Transaction::where('user_id', $user->id)
            ->where('type', 'expense')
            ->sum('amount');

        return [
            'recent_transactions' => $recentTransactions,
            'financial_summary' => [
                'total_income' => $totalIncome,
                'total_expenses' => $totalExpenses,
                'balance' => $totalIncome - $totalExpenses
            ]
        ];
    }
}