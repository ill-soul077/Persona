<?php

namespace Tests\Feature;

use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Transaction Feature Tests
 * 
 * Tests the transaction creation workflow and validation.
 */
class TransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed lookup tables
        $this->seed([
            \Database\Seeders\IncomeSourceSeeder::class,
            \Database\Seeders\ExpenseCategorySeeder::class,
        ]);

        $this->user = User::factory()->create();
    }

    /**
     * Test creating an expense transaction
     */
    public function test_can_create_expense_transaction(): void
    {
        $category = ExpenseCategory::where('slug', 'fast_food')->first();

        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 15.50,
            'currency' => 'USD',
            'date' => now(),
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'description' => 'Lunch at Burger King',
            'meta' => [
                'vendor' => 'Burger King',
                'location' => 'Downtown',
                'tax' => 1.25,
            ],
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 15.50,
        ]);

        $this->assertEquals('Burger King', $transaction->vendor);
        $this->assertEquals('Downtown', $transaction->location);
    }

    /**
     * Test creating an income transaction
     */
    public function test_can_create_income_transaction(): void
    {
        $source = IncomeSource::where('slug', 'from_home')->first();

        $transaction = Transaction::create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 500.00,
            'currency' => 'USD',
            'date' => now(),
            'category_id' => $source->id,
            'category_type' => IncomeSource::class,
            'description' => 'Monthly allowance',
        ]);

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'type' => 'income',
            'amount' => 500.00,
        ]);

        $this->assertTrue($transaction->isIncome());
        $this->assertFalse($transaction->isExpense());
    }

    /**
     * Test transaction amount formatting
     */
    public function test_formats_amount_with_currency(): void
    {
        $transaction = Transaction::factory()->create([
            'amount' => 123.45,
            'currency' => 'USD',
        ]);

        $this->assertEquals('USD 123.45', $transaction->formatted_amount);
    }

    /**
     * Test transaction scopes
     */
    public function test_can_filter_transactions_by_type(): void
    {
        Transaction::factory()->income()->count(5)->create(['user_id' => $this->user->id]);
        Transaction::factory()->expense()->count(10)->create(['user_id' => $this->user->id]);

        $incomeCount = Transaction::income()->count();
        $expenseCount = Transaction::expense()->count();

        $this->assertEquals(5, $incomeCount);
        $this->assertEquals(10, $expenseCount);
    }

    /**
     * Test transaction date range filtering
     */
    public function test_can_filter_transactions_by_date_range(): void
    {
        // Create transactions with different dates
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->subDays(10),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'date' => now()->subDays(5),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'date' => now(),
        ]);

        $recentTransactions = Transaction::dateRange(
            now()->subDays(7),
            now()
        )->count();

        $this->assertEquals(2, $recentTransactions);
    }

    /**
     * Test polymorphic category relationship
     */
    public function test_polymorphic_category_relationship_works(): void
    {
        $expenseCategory = ExpenseCategory::where('slug', 'food')->first();
        $incomeSource = IncomeSource::where('slug', 'freelance')->first();

        $expense = Transaction::factory()->create([
            'type' => 'expense',
            'category_id' => $expenseCategory->id,
            'category_type' => ExpenseCategory::class,
        ]);

        $income = Transaction::factory()->create([
            'type' => 'income',
            'category_id' => $incomeSource->id,
            'category_type' => IncomeSource::class,
        ]);

        $this->assertInstanceOf(ExpenseCategory::class, $expense->category);
        $this->assertInstanceOf(IncomeSource::class, $income->category);
    }
}
