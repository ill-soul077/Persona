<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Transaction;
use App\Models\ExpenseCategory;
use App\Models\IncomeSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;

class ChatFlowTest extends TestCase
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
    public function complete_chat_flow_creates_transaction()
    {
        $groceryCategory = ExpenseCategory::where('slug', 'groceries')->first();
        
        // Mock Gemini API response
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 500.00,
                                        'currency' => 'BDT',
                                        'category' => 'groceries',
                                        'vendor' => 'Agora',
                                        'date' => now()->format('Y-m-d'),
                                        'confidence' => 0.92
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Step 1: Parse finance text
        $parseResponse = $this->actingAs($this->user)
            ->postJson('/api/chat/parse-finance', [
                'text' => 'I spent 500 taka on groceries at Agora'
            ]);

        $parseResponse->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'type' => 'expense',
                    'amount' => 500.00,
                    'currency' => 'BDT',
                    'category' => 'groceries'
                ]
            ]);

        $parsedData = $parseResponse->json('data');

        // Step 2: Confirm transaction
        $confirmResponse = $this->actingAs($this->user)
            ->postJson('/api/chat/confirm-transaction', $parsedData);

        $confirmResponse->assertStatus(200)
            ->assertJson(['success' => true]);

        // Step 3: Verify database
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'expense',
            'amount' => 500.00,
            'currency' => 'BDT',
            'category_type' => ExpenseCategory::class,
            'category_id' => $groceryCategory->id
        ]);

        // Step 4: Verify AI log
        $this->assertDatabaseHas('ai_logs', [
            'user_id' => $this->user->id,
            'action' => 'parse_finance',
            'status' => 'success'
        ]);
    }

    /** @test */
    public function low_confidence_parsing_shows_warning()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 0,
                                        'currency' => 'BDT',
                                        'category' => 'unknown',
                                        'date' => now()->format('Y-m-d'),
                                        'confidence' => 0.35
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/chat/parse-finance', [
                'text' => 'bought stuff yesterday'
            ]);

        $response->assertStatus(200);
        $data = $response->json('data');

        $this->assertLessThan(0.6, $data['confidence']);
        $this->assertTrue($data['requires_confirmation'] ?? false);
    }

    /** @test */
    public function fallback_rules_work_when_gemini_fails()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([], 500)
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/chat/parse-finance', [
                'text' => 'Spent 500 BDT on groceries'
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'fallback_used' => true
            ]);

        $data = $response->json('data');
        $this->assertEquals(500.00, $data['amount']);
        $this->assertEquals('BDT', $data['currency']);
        $this->assertEquals('expense', $data['type']);
    }

    /** @test */
    public function unauthorized_user_cannot_parse()
    {
        $response = $this->postJson('/api/chat/parse-finance', [
            'text' => 'spent 500 taka'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function user_cannot_confirm_invalid_transaction_data()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/chat/confirm-transaction', [
                'type' => 'expense',
                'amount' => -500, // Invalid: negative amount
                'currency' => 'BDT'
            ]);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function income_transaction_creates_correctly()
    {
        $salarySource = IncomeSource::where('slug', 'salary')->first();

        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'income',
                                        'amount' => 50000.00,
                                        'currency' => 'BDT',
                                        'category' => 'salary',
                                        'date' => now()->format('Y-m-d'),
                                        'confidence' => 0.95
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $parseResponse = $this->actingAs($this->user)
            ->postJson('/api/chat/parse-finance', [
                'text' => 'I received 50000 taka as salary'
            ]);

        $parseResponse->assertStatus(200);
        $parsedData = $parseResponse->json('data');

        $confirmResponse = $this->actingAs($this->user)
            ->postJson('/api/chat/confirm-transaction', $parsedData);

        $confirmResponse->assertStatus(200);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'type' => 'income',
            'amount' => 50000.00,
            'category_type' => IncomeSource::class,
            'category_id' => $salarySource->id
        ]);
    }

    /** @test */
    public function transaction_with_vendor_saves_metadata()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 1500.00,
                                        'currency' => 'BDT',
                                        'category' => 'dining-out',
                                        'vendor' => 'Burger King',
                                        'description' => 'Dinner with friends',
                                        'date' => now()->format('Y-m-d'),
                                        'confidence' => 0.88
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $parseResponse = $this->actingAs($this->user)
            ->postJson('/api/chat/parse-finance', [
                'text' => 'Had dinner at Burger King for 1500 taka with friends'
            ]);

        $parsedData = $parseResponse->json('data');

        $this->actingAs($this->user)
            ->postJson('/api/chat/confirm-transaction', $parsedData);

        $transaction = Transaction::where('user_id', $this->user->id)->first();

        $this->assertNotNull($transaction);
        $this->assertEquals('Burger King', $transaction->meta['vendor'] ?? null);
        $this->assertStringContainsString('friends', $transaction->description ?? '');
    }

    /** @test */
    public function chat_handles_multiple_currencies()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 25.00,
                                        'currency' => 'USD',
                                        'category' => 'entertainment',
                                        'date' => now()->format('Y-m-d'),
                                        'confidence' => 0.90
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $parseResponse = $this->actingAs($this->user)
            ->postJson('/api/chat/parse-finance', [
                'text' => 'Movie tickets cost $25'
            ]);

        $parsedData = $parseResponse->json('data');

        $this->actingAs($this->user)
            ->postJson('/api/chat/confirm-transaction', $parsedData);

        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->user->id,
            'amount' => 25.00,
            'currency' => 'USD'
        ]);
    }

    /** @test */
    public function rate_limiting_prevents_spam()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 100.00,
                                        'currency' => 'BDT',
                                        'category' => 'groceries',
                                        'date' => now()->format('Y-m-d'),
                                        'confidence' => 0.85
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Make 61 requests (assuming 60/min rate limit)
        for ($i = 0; $i < 61; $i++) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/chat/parse-finance', [
                    'text' => "Spent 100 taka {$i}"
                ]);

            if ($i < 60) {
                $response->assertStatus(200);
            } else {
                $response->assertStatus(429); // Too Many Requests
            }
        }
    }
}
