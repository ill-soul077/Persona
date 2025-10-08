#!/usr/bin/env php
<?php

/**
 * Manual Testing Script for Finance Module
 * 
 * This script provides an interactive way to test the Finance Tracker
 * functionality including parsing, chat flow, and data aggregation.
 * 
 * Usage: php manual-test.php
 */

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use App\Services\GeminiService;
use Illuminate\Support\Facades\DB;

class ManualTester
{
    protected GeminiService $geminiService;
    protected ?User $testUser = null;

    public function __construct()
    {
        $this->geminiService = new GeminiService();
        $this->setupTestUser();
    }

    protected function setupTestUser(): void
    {
        $this->testUser = User::where('email', 'test@example.com')->first();
        
        if (!$this->testUser) {
            $this->testUser = User::create([
                'name' => 'Test User',
                'email' => 'test@example.com',
                'password' => bcrypt('password')
            ]);
            $this->log("✅ Created test user: test@example.com", 'success');
        } else {
            $this->log("✅ Using existing test user: test@example.com", 'info');
        }
    }

    public function run(): void
    {
        $this->printHeader();
        
        while (true) {
            $this->printMenu();
            $choice = $this->prompt("Enter your choice");

            switch ($choice) {
                case '1':
                    $this->testParsingAccuracy();
                    break;
                case '2':
                    $this->testChatFlow();
                    break;
                case '3':
                    $this->testChartAggregation();
                    break;
                case '4':
                    $this->testSamplePhrases();
                    break;
                case '5':
                    $this->testEdgeCases();
                    break;
                case '6':
                    $this->viewStatistics();
                    break;
                case '7':
                    $this->cleanupTestData();
                    break;
                case '0':
                    $this->log("\n👋 Goodbye!", 'info');
                    exit(0);
                default:
                    $this->log("❌ Invalid choice. Please try again.", 'error');
            }

            $this->prompt("\nPress Enter to continue...");
        }
    }

    protected function printHeader(): void
    {
        echo "\n";
        echo "╔════════════════════════════════════════════════════════════╗\n";
        echo "║     🧪 AI Personal Tracker - Manual Testing Suite         ║\n";
        echo "║                 Finance Module Testing                     ║\n";
        echo "╚════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    protected function printMenu(): void
    {
        echo "\n";
        echo "┌─────────────────── Test Menu ──────────────────────┐\n";
        echo "│                                                     │\n";
        echo "│  1. Test Parsing Accuracy (Single Phrase)          │\n";
        echo "│  2. Test Complete Chat Flow                        │\n";
        echo "│  3. Test Chart Data Aggregation                    │\n";
        echo "│  4. Run Sample Phrases Test Suite                  │\n";
        echo "│  5. Test Edge Cases                                │\n";
        echo "│  6. View Statistics                                │\n";
        echo "│  7. Cleanup Test Data                              │\n";
        echo "│  0. Exit                                            │\n";
        echo "│                                                     │\n";
        echo "└─────────────────────────────────────────────────────┘\n";
        echo "\n";
    }

    protected function testParsingAccuracy(): void
    {
        $this->log("\n🔍 Testing Parsing Accuracy", 'header');
        
        $text = $this->prompt("Enter a transaction phrase");
        
        if (empty($text)) {
            $this->log("❌ Empty input. Aborting.", 'error');
            return;
        }

        $this->log("⏳ Parsing...", 'info');
        $startTime = microtime(true);

        try {
            $result = $this->geminiService->parseFinanceText($text, $this->testUser->id);
            $duration = round((microtime(true) - $startTime) * 1000, 2);

            $this->log("\n✅ Parse Results (took {$duration}ms):", 'success');
            $this->printResult($result);

            // Confidence assessment
            $confidence = $result['confidence'] ?? 0;
            if ($confidence >= 0.9) {
                $this->log("🎯 High Confidence: {$confidence}", 'success');
            } elseif ($confidence >= 0.7) {
                $this->log("⚠️  Medium Confidence: {$confidence}", 'warning');
            } else {
                $this->log("❌ Low Confidence: {$confidence} - Review Required", 'error');
            }

        } catch (\Exception $e) {
            $this->log("❌ Parsing failed: " . $e->getMessage(), 'error');
        }
    }

    protected function testChatFlow(): void
    {
        $this->log("\n💬 Testing Complete Chat Flow", 'header');
        
        $text = $this->prompt("Enter transaction phrase");
        
        if (empty($text)) {
            return;
        }

        // Step 1: Parse
        $this->log("\n📝 Step 1: Parsing...", 'info');
        try {
            $parsedData = $this->geminiService->parseFinanceText($text, $this->testUser->id);
            $this->printResult($parsedData);
        } catch (\Exception $e) {
            $this->log("❌ Parse failed: " . $e->getMessage(), 'error');
            return;
        }

        // Step 2: Preview
        $this->log("\n👀 Step 2: Preview", 'info');
        $confirm = $this->prompt("Confirm this transaction? (yes/no)");
        
        if (strtolower($confirm) !== 'yes') {
            $this->log("❌ Transaction cancelled by user", 'warning');
            return;
        }

        // Step 3: Save to database
        $this->log("\n💾 Step 3: Saving to database...", 'info');
        try {
            $categoryType = $parsedData['type'] === 'income' 
                ? IncomeSource::class 
                : ExpenseCategory::class;
            
            $categorySlug = $parsedData['category'] ?? 'groceries';
            $category = $categoryType::where('slug', $categorySlug)->first();

            if (!$category) {
                $this->log("⚠️  Category '{$categorySlug}' not found, using first available", 'warning');
                $category = $categoryType::first();
            }

            $transaction = Transaction::create([
                'user_id' => $this->testUser->id,
                'type' => $parsedData['type'],
                'amount' => $parsedData['amount'],
                'currency' => $parsedData['currency'] ?? 'BDT',
                'date' => $parsedData['date'] ?? now(),
                'category_type' => $categoryType,
                'category_id' => $category->id,
                'description' => $parsedData['description'] ?? null,
                'meta' => [
                    'vendor' => $parsedData['vendor'] ?? null,
                    'confidence' => $parsedData['confidence'] ?? 0
                ]
            ]);

            $this->log("✅ Transaction saved! ID: {$transaction->id}", 'success');
            $this->printResult($transaction->toArray());

        } catch (\Exception $e) {
            $this->log("❌ Save failed: " . $e->getMessage(), 'error');
        }
    }

    protected function testChartAggregation(): void
    {
        $this->log("\n📊 Testing Chart Data Aggregation", 'header');
        
        // Get expense breakdown
        $expenseBreakdown = Transaction::where('user_id', $this->testUser->id)
            ->where('type', 'expense')
            ->join('expense_categories', function($join) {
                $join->on('transactions.category_id', '=', 'expense_categories.id')
                     ->where('transactions.category_type', '=', ExpenseCategory::class);
            })
            ->select('expense_categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('expense_categories.name')
            ->get();

        if ($expenseBreakdown->isEmpty()) {
            $this->log("⚠️  No expense data found for aggregation", 'warning');
            return;
        }

        $total = $expenseBreakdown->sum('total');
        
        $this->log("\n📈 Expense Breakdown:", 'info');
        $this->log("Total Expenses: ৳" . number_format($total, 2), 'info');
        $this->log(str_repeat("-", 60), 'info');

        $percentageSum = 0;
        foreach ($expenseBreakdown as $item) {
            $percentage = ($item->total / $total) * 100;
            $percentageSum += $percentage;
            
            $bar = str_repeat("█", (int)($percentage / 2));
            $this->log(sprintf(
                "%-20s ৳%10s  %5.1f%%  %s",
                $item->name,
                number_format($item->total, 2),
                $percentage,
                $bar
            ), 'info');
        }

        $this->log(str_repeat("-", 60), 'info');
        $this->log("Total Percentage: " . round($percentageSum, 2) . "%", 'info');
        
        // Validation
        if (abs($percentageSum - 100) < 0.01) {
            $this->log("✅ Percentages sum to 100% correctly", 'success');
        } else {
            $this->log("❌ Percentages don't sum to 100% (off by " . round(100 - $percentageSum, 2) . "%)", 'error');
        }
    }

    protected function testSamplePhrases(): void
    {
        $this->log("\n🧪 Running Sample Phrases Test Suite", 'header');
        
        $phrases = [
            // Income
            "I received 50000 taka as salary",
            "Got paid $1500 for freelance work",
            "Bonus of 25000 BDT",
            
            // Expense
            "Spent 500 taka on groceries at Agora",
            "Paid 1200 for electricity bill",
            "Movie tickets cost $15",
            
            // Edge cases
            "bought stuff yesterday",
            "500",
        ];

        $results = [
            'success' => 0,
            'medium_confidence' => 0,
            'low_confidence' => 0,
            'failed' => 0
        ];

        foreach ($phrases as $index => $phrase) {
            $this->log("\n[" . ($index + 1) . "/" . count($phrases) . "] Testing: \"{$phrase}\"", 'info');
            
            try {
                $result = $this->geminiService->parseFinanceText($phrase, $this->testUser->id);
                $confidence = $result['confidence'] ?? 0;

                if ($confidence >= 0.8) {
                    $results['success']++;
                    $this->log("  ✅ High confidence: {$confidence}", 'success');
                } elseif ($confidence >= 0.6) {
                    $results['medium_confidence']++;
                    $this->log("  ⚠️  Medium confidence: {$confidence}", 'warning');
                } else {
                    $results['low_confidence']++;
                    $this->log("  ❌ Low confidence: {$confidence}", 'error');
                }
            } catch (\Exception $e) {
                $results['failed']++;
                $this->log("  ❌ Failed: " . $e->getMessage(), 'error');
            }

            usleep(100000); // 100ms delay to avoid rate limiting
        }

        // Summary
        $this->log("\n📊 Test Summary:", 'header');
        $this->log("  Success (≥0.8):     {$results['success']}", 'success');
        $this->log("  Medium (≥0.6):      {$results['medium_confidence']}", 'warning');
        $this->log("  Low (<0.6):         {$results['low_confidence']}", 'error');
        $this->log("  Failed:             {$results['failed']}", 'error');
        
        $accuracy = ($results['success'] / count($phrases)) * 100;
        $this->log("\n  Overall Accuracy: " . round($accuracy, 1) . "%", 
            $accuracy >= 80 ? 'success' : 'warning');
    }

    protected function testEdgeCases(): void
    {
        $this->log("\n⚠️  Testing Edge Cases", 'header');
        
        $edgeCases = [
            'Empty string' => '',
            'Only amount' => '500',
            'No amount' => 'bought groceries',
            'Negative amount' => 'spent -500 taka',
            'Zero amount' => 'paid 0 BDT',
            'Very large number' => 'salary 9999999999 taka',
            'Special characters' => 'bought milk @ 120 taka!',
            'Mixed language' => 'ভাত কিনলাম 500 taka',
        ];

        foreach ($edgeCases as $label => $phrase) {
            $this->log("\n🧪 {$label}: \"{$phrase}\"", 'info');
            
            try {
                $result = $this->geminiService->parseFinanceText($phrase, $this->testUser->id);
                $confidence = $result['confidence'] ?? 0;
                
                if ($confidence < 0.5) {
                    $this->log("  ✅ Correctly flagged as low confidence: {$confidence}", 'success');
                } else {
                    $this->log("  ⚠️  Unexpectedly high confidence: {$confidence}", 'warning');
                    $this->printResult($result);
                }
            } catch (\Exception $e) {
                $this->log("  ✅ Correctly rejected: " . $e->getMessage(), 'success');
            }
        }
    }

    protected function viewStatistics(): void
    {
        $this->log("\n📊 Database Statistics", 'header');
        
        $totalTransactions = Transaction::where('user_id', $this->testUser->id)->count();
        $totalIncome = Transaction::where('user_id', $this->testUser->id)
            ->where('type', 'income')
            ->sum('amount');
        $totalExpense = Transaction::where('user_id', $this->testUser->id)
            ->where('type', 'expense')
            ->sum('amount');
        $balance = $totalIncome - $totalExpense;

        $this->log("\n💰 Financial Summary:", 'info');
        $this->log("  Total Transactions:  {$totalTransactions}", 'info');
        $this->log("  Total Income:        ৳" . number_format($totalIncome, 2), 'success');
        $this->log("  Total Expense:       ৳" . number_format($totalExpense, 2), 'error');
        $this->log("  Balance:             ৳" . number_format($balance, 2), 
            $balance >= 0 ? 'success' : 'error');

        if ($totalIncome > 0) {
            $savingsRate = ($balance / $totalIncome) * 100;
            $this->log("  Savings Rate:        " . round($savingsRate, 1) . "%", 'info');
        }

        // Recent transactions
        $recent = Transaction::where('user_id', $this->testUser->id)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        if ($recent->isNotEmpty()) {
            $this->log("\n📋 Recent Transactions:", 'info');
            foreach ($recent as $t) {
                $symbol = $t->type === 'income' ? '+' : '-';
                $color = $t->type === 'income' ? 'success' : 'error';
                $this->log("  {$symbol}৳{$t->amount} - {$t->category?->name} - {$t->date->format('M d')}", $color);
            }
        }
    }

    protected function cleanupTestData(): void
    {
        $this->log("\n🗑️  Cleanup Test Data", 'header');
        
        $confirm = $this->prompt("Delete all test transactions? (yes/no)");
        
        if (strtolower($confirm) === 'yes') {
            $count = Transaction::where('user_id', $this->testUser->id)->count();
            Transaction::where('user_id', $this->testUser->id)->delete();
            $this->log("✅ Deleted {$count} transactions", 'success');
        } else {
            $this->log("❌ Cleanup cancelled", 'warning');
        }
    }

    protected function printResult(array $data): void
    {
        echo "┌" . str_repeat("─", 58) . "┐\n";
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $value = $value ?? 'null';
            echo sprintf("│ %-20s : %-34s │\n", $key, substr($value, 0, 34));
        }
        echo "└" . str_repeat("─", 58) . "┘\n";
    }

    protected function prompt(string $message): string
    {
        echo "➤ {$message}: ";
        return trim(fgets(STDIN));
    }

    protected function log(string $message, string $type = 'info'): void
    {
        $colors = [
            'success' => "\033[32m",
            'error' => "\033[31m",
            'warning' => "\033[33m",
            'info' => "\033[36m",
            'header' => "\033[35m\033[1m",
            'reset' => "\033[0m"
        ];

        $color = $colors[$type] ?? $colors['info'];
        echo $color . $message . $colors['reset'] . "\n";
    }
}

// Run the tester
$tester = new ManualTester();
$tester->run();
