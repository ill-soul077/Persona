<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_loads_with_database_data()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Seed categories
        $this->seed(\Database\Seeders\IncomeSourceSeeder::class);
        $this->seed(\Database\Seeders\ExpenseCategorySeeder::class);
        
        $incomeSource = IncomeSource::first();
        $expenseCategory = ExpenseCategory::first();
        
        // Create some test transactions
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'income',
            'amount' => 5000,
            'currency' => 'BDT',
            'date' => now(),
            'category_id' => $incomeSource->id,
            'category_type' => IncomeSource::class,
            'description' => 'Test income'
        ]);
        
        Transaction::create([
            'user_id' => $user->id,
            'type' => 'expense',
            'amount' => 1500,
            'currency' => 'BDT',
            'date' => now(),
            'category_id' => $expenseCategory->id,
            'category_type' => ExpenseCategory::class,
            'description' => 'Test expense'
        ]);

        // Access dashboard as authenticated user
        $response = $this->actingAs($user)->get('/dashboard');

        // Should load successfully
        $response->assertStatus(200);
        
        // Should contain financial data
        $response->assertViewHas('balance');
        $response->assertViewHas('monthlyIncome');
        $response->assertViewHas('monthlyExpenses');
        $response->assertViewHas('recentTransactions');
        $response->assertViewHas('expenseDistribution');
        $response->assertViewHas('weeklyTrend');
        
        // Verify the balance is calculated correctly from database
        $viewData = $response->viewData('balance');
        $this->assertEquals(3500, $viewData); // 5000 income - 1500 expense
    }

    public function test_all_dashboard_links_are_defined()
    {
        $user = User::factory()->create();
        
        // Get all routes that should exist for dashboard links
        $expectedRoutes = [
            'chatbot',
            'finance.transactions.create',
            'finance.transactions.index',
            'tasks.create',
            'tasks.index',
            'finance.reports',
            'profile.show'
        ];
        
        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                \Illuminate\Support\Facades\Route::has($routeName),
                "Route '{$routeName}' is not defined but is used in dashboard"
            );
        }
    }
}