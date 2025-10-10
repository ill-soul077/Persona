<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SingleRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_transaction_create_route()
    {
        // Create a test user
        $user = User::factory()->create();
        
        // Seed required data
        $this->seed(\Database\Seeders\IncomeSourceSeeder::class);
        $this->seed(\Database\Seeders\ExpenseCategorySeeder::class);
        
        // Test the transaction create route specifically
        $response = $this->actingAs($user)->get(route('finance.transactions.create'));
        
        if ($response->status() !== 200) {
            // Dump the error content to see what's wrong
            dump($response->getContent());
            dump($response->status());
        }
        
        $response->assertStatus(200);
    }
}