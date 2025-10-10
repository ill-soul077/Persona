<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardLinksTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_links_are_accessible()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Seed required data for pages that need it
        $this->seed(\Database\Seeders\IncomeSourceSeeder::class);
        $this->seed(\Database\Seeders\ExpenseCategorySeeder::class);

        $linksToTest = [
            ['route' => 'chatbot', 'method' => 'GET'],
            ['route' => 'finance.transactions.create', 'method' => 'GET'],
            ['route' => 'finance.transactions.index', 'method' => 'GET'],
            ['route' => 'tasks.create', 'method' => 'GET'],
            ['route' => 'tasks.index', 'method' => 'GET'],
            ['route' => 'finance.reports', 'method' => 'GET'],
            ['route' => 'profile.show', 'method' => 'GET'], // This is an alias to profile.edit
        ];

        foreach ($linksToTest as $link) {
            $routeName = $link['route'];
            $method = $link['method'];
            
            // Test that each route is accessible
            $response = $this->actingAs($user)->{strtolower($method)}(route($routeName));
            
            $this->assertTrue(
                $response->status() < 400,
                "Route '{$routeName}' returned status {$response->status()}, expected < 400"
            );
        }
    }

    public function test_dashboard_chart_data_endpoint_works()
    {
        $user = User::factory()->create();
        
        // Test the chart data endpoint used by dashboard
        $response = $this->actingAs($user)->get('/dashboard/chart-data?type=expense-distribution');
        $response->assertStatus(200);
        $response->assertJson([]);
        
        $response = $this->actingAs($user)->get('/dashboard/chart-data?type=weekly-trend');
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'expenses',
            'income'
        ]);
    }
}