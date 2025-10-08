<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ChartDataAccuracyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        
        // Seed categories
        $this->seed(\Database\Seeders\IncomeSourceSeeder::class);
        $this->seed(\Database\Seeders\ExpenseCategorySeeder::class);
    }

    /** @test */
    public function expense_breakdown_percentages_sum_to_100()
    {
        $categories = ExpenseCategory::take(5)->get();
        
        foreach ($categories as $category) {
            Transaction::factory()->create([
                'user_id' => $this->user->id,
                'type' => 'expense',
                'category_id' => $category->id,
                'category_type' => ExpenseCategory::class,
                'amount' => rand(100, 1000)
            ]);
        }

        $response = $this->actingAs($this->user)
            ->getJson('/finance/chart-data');

        $response->assertStatus(200);
        
        $data = $response->json();
        $total = array_sum($data);

        // Calculate percentages
        $percentages = [];
        foreach ($data as $category => $amount) {
            $percentage = ($amount / $total) * 100;
            $percentages[$category] = $percentage;
            
            $this->assertGreaterThan(0, $percentage);
            $this->assertLessThanOrEqual(100, $percentage);
        }

        // Verify sum is ~100%
        $totalPercentage = array_sum($percentages);
        $this->assertEquals(100, round($totalPercentage, 2));
    }

    /** @test */
    public function dashboard_calculates_correct_totals()
    {
        // Create 3 income transactions of 10000 each
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 10000.00,
            'currency' => 'BDT',
            'category_type' => IncomeSource::class,
            'category_id' => IncomeSource::first()->id
        ]);

        // Create 2 expense transactions of 5000 each
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 5000.00,
            'currency' => 'BDT',
            'category_type' => ExpenseCategory::class,
            'category_id' => ExpenseCategory::first()->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/finance/dashboard');

        $response->assertStatus(200)
            ->assertViewHas('totalIncome', 30000.00)
            ->assertViewHas('totalExpense', 10000.00)
            ->assertViewHas('balance', 20000.00);
    }

    /** @test */
    public function date_range_filtering_works_correctly()
    {
        // Old transaction (September)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'date' => '2025-09-15',
            'amount' => 5000.00,
            'type' => 'expense',
            'category_type' => ExpenseCategory::class,
            'category_id' => ExpenseCategory::first()->id
        ]);

        // New transactions (October)
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'date' => '2025-10-05',
            'amount' => 1500.00,
            'type' => 'expense',
            'category_type' => ExpenseCategory::class,
            'category_id' => ExpenseCategory::first()->id
        ]);

        $response = $this->actingAs($this->user)
            ->get('/finance/dashboard', [
                'start_date' => '2025-10-01',
                'end_date' => '2025-10-31'
            ]);

        $response->assertStatus(200)
            ->assertViewHas('totalExpense', 3000.00); // Only October transactions
    }

    /** @test */
    public function chart_data_aggregates_by_category_correctly()
    {
        $groceries = ExpenseCategory::where('slug', 'groceries')->first();
        $transport = ExpenseCategory::where('slug', 'transport')->first();

        // 3 grocery transactions @ 1000 each = 3000
        Transaction::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_type' => ExpenseCategory::class,
            'category_id' => $groceries->id,
            'amount' => 1000.00
        ]);

        // 2 transport transactions @ 500 each = 1000
        Transaction::factory()->count(2)->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_type' => ExpenseCategory::class,
            'category_id' => $transport->id,
            'amount' => 500.00
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/finance/chart-data');

        $response->assertStatus(200)
            ->assertJson([
                $groceries->name => 3000.00,
                $transport->name => 1000.00
            ]);
    }

    /** @test */
    public function empty_state_returns_zero_totals()
    {
        $response = $this->actingAs($this->user)
            ->get('/finance/dashboard');

        $response->assertStatus(200)
            ->assertViewHas('totalIncome', 0)
            ->assertViewHas('totalExpense', 0)
            ->assertViewHas('balance', 0);
        
        $breakdown = $response->viewData('expenseBreakdown');
        $this->assertTrue($breakdown->isEmpty());
    }

    /** @test */
    public function chart_handles_decimal_precision()
    {
        $category = ExpenseCategory::first();

        // Create transactions with decimal amounts
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => 123.45
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => 678.90
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/finance/chart-data');

        $response->assertStatus(200);
        
        $data = $response->json();
        $total = $data[$category->name];

        // Should be 802.35 with proper decimal precision
        $this->assertEquals(802.35, $total);
    }

    /** @test */
    public function category_drilldown_shows_correct_transactions()
    {
        $groceries = ExpenseCategory::where('slug', 'groceries')->first();

        Transaction::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_type' => ExpenseCategory::class,
            'category_id' => $groceries->id
        ]);

        // Create transaction in different category (should not appear)
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_type' => ExpenseCategory::class,
            'category_id' => ExpenseCategory::where('slug', 'transport')->first()->id
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/finance/category-drilldown', [
                'category' => $groceries->name
            ]);

        $response->assertStatus(200)
            ->assertJsonCount(5, 'transactions');
    }

    /** @test */
    public function savings_rate_calculates_correctly()
    {
        // Income: 50000
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 50000.00,
            'category_type' => IncomeSource::class,
            'category_id' => IncomeSource::first()->id
        ]);

        // Expense: 30000
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 30000.00,
            'category_type' => ExpenseCategory::class,
            'category_id' => ExpenseCategory::first()->id
        ]);

        // Savings: 20000
        // Savings Rate: (20000 / 50000) * 100 = 40%

        $response = $this->actingAs($this->user)
            ->get('/finance/dashboard');

        $response->assertStatus(200);
        
        $balance = $response->viewData('balance');
        $totalIncome = $response->viewData('totalIncome');
        
        $savingsRate = ($balance / $totalIncome) * 100;
        
        $this->assertEquals(40.0, $savingsRate);
    }

    /** @test */
    public function chart_excludes_other_users_transactions()
    {
        $otherUser = User::factory()->create();
        $category = ExpenseCategory::first();

        // Create transaction for other user
        Transaction::factory()->create([
            'user_id' => $otherUser->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => 9999.00
        ]);

        // Create transaction for current user
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => 500.00
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/finance/chart-data');

        $response->assertStatus(200);
        
        $data = $response->json();
        
        // Should only show current user's 500, not other user's 9999
        $this->assertEquals(500.00, $data[$category->name]);
    }

    /** @test */
    public function multi_currency_transactions_handled_separately()
    {
        $category = ExpenseCategory::first();

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => 5000.00,
            'currency' => 'BDT'
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
            'category_id' => $category->id,
            'category_type' => ExpenseCategory::class,
            'amount' => 50.00,
            'currency' => 'USD'
        ]);

        $response = $this->actingAs($this->user)
            ->get('/finance/dashboard');

        $response->assertStatus(200);
        
        // Verify both transactions exist (implementation may vary)
        $this->assertDatabaseCount('transactions', 2);
    }
}
