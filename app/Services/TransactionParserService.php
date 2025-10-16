<?php

namespace App\Services;

use App\Models\ExpenseCategory;
use App\Models\IncomeSource;

class TransactionParserService
{
    private $expenseKeywords = [
        'food_dining' => ['coffee', 'tea', 'lunch', 'dinner', 'breakfast', 'restaurant', 'food', 'meal', 'snack', 'drank', 'ate', 'pizza', 'burger'],
        'transportation' => ['taxi', 'bus', 'uber', 'train', 'fuel', 'gas', 'parking', 'transport', 'ride', 'petrol'],
        'shopping' => ['bought', 'purchase', 'shopping', 'clothes', 'shoes', 'book', 'store', 'market'],
        'entertainment' => ['movie', 'cinema', 'game', 'party', 'concert', 'show', 'entertainment'],
        'utilities' => ['electricity', 'water', 'internet', 'phone', 'bill', 'utility'],
        'healthcare' => ['doctor', 'medicine', 'hospital', 'pharmacy', 'medical', 'health'],
        'education' => ['course', 'book', 'tuition', 'school', 'education', 'training'],
        'personal_care' => ['haircut', 'salon', 'cosmetics', 'personal', 'care'],
    ];

    private $incomeKeywords = [
        'salary' => ['salary', 'paycheck', 'wage', 'income'],
        'freelance' => ['freelance', 'project', 'client work', 'gig'],
        'business' => ['business', 'profit', 'revenue', 'sales'],
        'investments' => ['dividend', 'interest', 'investment', 'return'],
        'other_income' => ['received', 'earned', 'bonus', 'gift'],
    ];

    public function parseTransaction(string $message): array
    {
        $message = strtolower(trim($message));
        
        // Extract amount
        $amount = $this->extractAmount($message);
        
        // Detect transaction type
        $type = $this->detectType($message);
        
        // Identify category
        $category = $this->identifyCategory($message, $type);
        
        // Extract description
        $description = $this->extractDescription($message, $amount);

        return [
            'amount' => $amount,
            'type' => $type,
            'category' => $category,
            'description' => $description,
            'confidence' => $this->calculateConfidence($amount, $type, $category),
            'date' => now()->format('Y-m-d')
        ];
    }

    private function extractAmount(string $message): ?float
    {
        // Patterns for various amount formats
        $patterns = [
            '/(\d+(?:\.\d{1,2})?)\s*(?:taka|tk|৳)/',
            '/(?:taka|tk|৳)\s*(\d+(?:\.\d{1,2})?)/',
            '/₹\s*(\d+(?:\.\d{1,2})?)/',
            '/\$\s*(\d+(?:\.\d{1,2})?)/',
            '/(\d+(?:\.\d{1,2})?)\s*(?:dollars?|bucks?)/',
            '/(\d+(?:\.\d{1,2})?)\s*(?:rupees?)/',
            '/(\d+(?:\.\d{1,2})?)/', // Simple number extraction
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message, $matches)) {
                return (float) $matches[1];
            }
        }

        return null;
    }

    private function detectType(string $message): string
    {
        $expenseWords = ['spent', 'paid', 'bought', 'purchase', 'cost', 'expense', 'drank', 'ate', 'used', 'bill'];
        $incomeWords = ['earned', 'received', 'got', 'income', 'salary', 'payment', 'bonus', 'profit'];

        foreach ($incomeWords as $word) {
            if (strpos($message, $word) !== false) {
                return 'income';
            }
        }

        foreach ($expenseWords as $word) {
            if (strpos($message, $word) !== false) {
                return 'expense';
            }
        }

        return 'expense'; // Default to expense
    }

    private function identifyCategory(string $message, string $type): ?array
    {
        $keywords = $type === 'expense' ? $this->expenseKeywords : $this->incomeKeywords;

        foreach ($keywords as $categoryType => $words) {
            foreach ($words as $word) {
                if (strpos($message, $word) !== false) {
                    return $this->getCategoryFromDatabase($categoryType, $type);
                }
            }
        }

        return null;
    }

    private function getCategoryFromDatabase(string $categoryType, string $type): ?array
    {
        if ($type === 'expense') {
            // Try to find category by slug or name
            $category = ExpenseCategory::where('slug', $categoryType)
                                     ->orWhere('name', 'like', '%' . str_replace('_', ' ', $categoryType) . '%')
                                     ->first();
            
            if (!$category) {
                // Get first available category as fallback
                $category = ExpenseCategory::first();
            }

            return $category ? [
                'id' => $category->id,
                'name' => $category->name,
                'type' => 'expense_category'
            ] : null;
        } else {
            // For income
            $source = IncomeSource::where('slug', $categoryType)
                                 ->orWhere('name', 'like', '%' . str_replace('_', ' ', $categoryType) . '%')
                                 ->first();
            
            if (!$source) {
                $source = IncomeSource::first();
            }

            return $source ? [
                'id' => $source->id,
                'name' => $source->name,
                'type' => 'income_source'
            ] : null;
        }
    }

    private function extractDescription(string $message, ?float $amount): string
    {
        // Remove amount patterns from message to get description
        $description = $message;
        
        // Remove common amount patterns
        $patterns = [
            '/\d+(?:\.\d{1,2})?\s*(?:taka|tk|৳|₹|\$|dollars?|bucks?|rupees?)/',
            '/(?:taka|tk|৳|₹|\$)\s*\d+(?:\.\d{1,2})?/',
        ];

        foreach ($patterns as $pattern) {
            $description = preg_replace($pattern, '', $description);
        }

        // Clean up and return
        return trim($description) ?: 'Transaction';
    }

    private function calculateConfidence(?float $amount, string $type, ?array $category): float
    {
        $confidence = 0;

        if ($amount !== null) {
            $confidence += 40;
        }

        if ($type) {
            $confidence += 30;
        }

        if ($category) {
            $confidence += 30;
        }

        return $confidence / 100;
    }

    public function isTransactionMessage(string $message): bool
    {
        $message = strtolower($message);
        
        // Check if message contains amount
        $hasAmount = preg_match('/\d+(?:\.\d{1,2})?/', $message);
        
        // Check for transaction keywords
        $transactionWords = ['spent', 'paid', 'bought', 'cost', 'earned', 'received', 'expense', 'income'];
        $hasTransactionWord = false;
        
        foreach ($transactionWords as $word) {
            if (strpos($message, $word) !== false) {
                $hasTransactionWord = true;
                break;
            }
        }

        return $hasAmount || $hasTransactionWord;
    }
}