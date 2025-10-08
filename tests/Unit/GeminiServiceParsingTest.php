<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\GeminiService;
use App\Models\User;
use App\Models\IncomeSource;
use App\Models\ExpenseCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class GeminiServiceParsingTest extends TestCase
{
    use RefreshDatabase;

    protected GeminiService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeminiService();
        $this->user = User::factory()->create();
        
        // Seed categories for testing
        $this->seed(\Database\Seeders\IncomeSourceSeeder::class);
        $this->seed(\Database\Seeders\ExpenseCategorySeeder::class);
    }

    /** @test */
    public function it_parses_basic_income_with_salary()
    {
        $this->mockGeminiResponse([
            'type' => 'income',
            'amount' => 50000.00,
            'currency' => 'BDT',
            'category' => 'salary',
            'date' => now()->format('Y-m-d'),
            'confidence' => 0.95
        ]);

        $result = $this->service->parseFinanceText(
            "I received 50000 taka as salary",
            $this->user->id
        );

        $this->assertEquals('income', $result['type']);
        $this->assertEquals(50000.00, $result['amount']);
        $this->assertEquals('BDT', $result['currency']);
        $this->assertEquals('salary', $result['category']);
        $this->assertGreaterThanOrEqual(0.9, $result['confidence']);
    }

    /** @test */
    public function it_parses_freelance_income_in_usd()
    {
        $this->mockGeminiResponse([
            'type' => 'income',
            'amount' => 1500.00,
            'currency' => 'USD',
            'category' => 'freelance',
            'date' => now()->format('Y-m-d'),
            'confidence' => 0.90
        ]);

        $result = $this->service->parseFinanceText(
            "Got paid $1500 for freelance work",
            $this->user->id
        );

        $this->assertEquals('income', $result['type']);
        $this->assertEquals(1500.00, $result['amount']);
        $this->assertEquals('USD', $result['currency']);
        $this->assertEquals('freelance', $result['category']);
    }

    /** @test */
    public function it_parses_grocery_expense_with_vendor()
    {
        $this->mockGeminiResponse([
            'type' => 'expense',
            'amount' => 500.00,
            'currency' => 'BDT',
            'category' => 'groceries',
            'vendor' => 'Agora',
            'date' => now()->format('Y-m-d'),
            'confidence' => 0.92
        ]);

        $result = $this->service->parseFinanceText(
            "Spent 500 taka on groceries at Agora",
            $this->user->id
        );

        $this->assertEquals('expense', $result['type']);
        $this->assertEquals(500.00, $result['amount']);
        $this->assertEquals('groceries', $result['category']);
        $this->assertEquals('Agora', $result['vendor']);
    }

    /** @test */
    public function it_parses_utility_bill_expense()
    {
        $this->mockGeminiResponse([
            'type' => 'expense',
            'amount' => 1200.00,
            'currency' => 'BDT',
            'category' => 'utilities',
            'subcategory' => 'electricity',
            'date' => now()->format('Y-m-d'),
            'confidence' => 0.85
        ]);

        $result = $this->service->parseFinanceText(
            "Paid 1200 for electricity bill",
            $this->user->id
        );

        $this->assertEquals('expense', $result['type']);
        $this->assertEquals(1200.00, $result['amount']);
        $this->assertEquals('utilities', $result['category']);
    }

    /** @test */
    public function it_parses_relative_date_yesterday()
    {
        $this->mockGeminiResponse([
            'type' => 'expense',
            'amount' => 500.00,
            'currency' => 'BDT',
            'category' => 'groceries',
            'date' => now()->subDay()->format('Y-m-d'),
            'confidence' => 0.88
        ]);

        $result = $this->service->parseFinanceText(
            "Yesterday I spent 500 taka on groceries",
            $this->user->id
        );

        $this->assertEquals(now()->subDay()->format('Y-m-d'), $result['date']);
    }

    /** @test */
    public function it_detects_bdt_currency_variations()
    {
        $variations = [
            "500 taka" => 'BDT',
            "৳500" => 'BDT',
            "500 BDT" => 'BDT',
            "Tk 500" => 'BDT',
        ];

        foreach ($variations as $phrase => $expectedCurrency) {
            $this->mockGeminiResponse([
                'type' => 'expense',
                'amount' => 500.00,
                'currency' => $expectedCurrency,
                'category' => 'groceries',
                'date' => now()->format('Y-m-d'),
                'confidence' => 0.85
            ]);

            $result = $this->service->parseFinanceText(
                "Spent {$phrase} on groceries",
                $this->user->id
            );

            $this->assertEquals($expectedCurrency, $result['currency'], "Failed for phrase: {$phrase}");
        }
    }

    /** @test */
    public function it_detects_usd_currency_variations()
    {
        $variations = [
            '$50' => 'USD',
            '50 dollars' => 'USD',
            '50 USD' => 'USD',
        ];

        foreach ($variations as $phrase => $expectedCurrency) {
            $this->mockGeminiResponse([
                'type' => 'expense',
                'amount' => 50.00,
                'currency' => $expectedCurrency,
                'category' => 'groceries',
                'date' => now()->format('Y-m-d'),
                'confidence' => 0.85
            ]);

            $result = $this->service->parseFinanceText(
                "Spent {$phrase} on groceries",
                $this->user->id
            );

            $this->assertEquals($expectedCurrency, $result['currency'], "Failed for phrase: {$phrase}");
        }
    }

    /** @test */
    public function it_returns_low_confidence_for_ambiguous_input()
    {
        $this->mockGeminiResponse([
            'type' => 'expense',
            'amount' => 0,
            'currency' => 'BDT',
            'category' => 'unknown',
            'date' => now()->format('Y-m-d'),
            'confidence' => 0.35
        ]);

        $result = $this->service->parseFinanceText(
            "bought stuff",
            $this->user->id
        );

        $this->assertLessThan(0.6, $result['confidence']);
        $this->assertTrue($result['requires_confirmation'] ?? false);
    }

    /** @test */
    public function it_uses_fallback_rules_when_gemini_fails()
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([], 500)
        ]);

        $result = $this->service->parseFinanceText(
            "Spent 500 BDT on groceries",
            $this->user->id
        );

        $this->assertEquals(500.00, $result['amount']);
        $this->assertEquals('BDT', $result['currency']);
        $this->assertEquals('expense', $result['type']);
        $this->assertTrue($result['fallback_used'] ?? false);
    }

    /** @test */
    public function it_extracts_amount_from_various_formats()
    {
        $formats = [
            '500.50' => 500.50,
            '1,234.56' => 1234.56,
            '৳500' => 500.00,
            '$20' => 20.00,
            'Tk 1500' => 1500.00,
        ];

        foreach ($formats as $input => $expected) {
            $this->mockGeminiResponse([
                'type' => 'expense',
                'amount' => $expected,
                'currency' => 'BDT',
                'category' => 'groceries',
                'date' => now()->format('Y-m-d'),
                'confidence' => 0.85
            ]);

            $result = $this->service->parseFinanceText(
                "Spent {$input} on groceries",
                $this->user->id
            );

            $this->assertEquals($expected, $result['amount'], "Failed for format: {$input}");
        }
    }

    /** @test */
    public function it_maps_category_keywords_correctly()
    {
        $mappings = [
            'grocery store' => 'groceries',
            'uber ride' => 'transport',
            'netflix subscription' => 'entertainment',
            'doctor visit' => 'healthcare',
            'electricity bill' => 'utilities',
        ];

        foreach ($mappings as $phrase => $expectedCategory) {
            $this->mockGeminiResponse([
                'type' => 'expense',
                'amount' => 100.00,
                'currency' => 'BDT',
                'category' => $expectedCategory,
                'date' => now()->format('Y-m-d'),
                'confidence' => 0.85
            ]);

            $result = $this->service->parseFinanceText(
                "Spent 100 taka on {$phrase}",
                $this->user->id
            );

            $this->assertEquals($expectedCategory, $result['category'], "Failed for phrase: {$phrase}");
        }
    }

    /** @test */
    public function it_caches_parsing_results()
    {
        $text = "Spent 500 taka on groceries";
        
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn([
                'type' => 'expense',
                'amount' => 500.00,
                'currency' => 'BDT',
                'category' => 'groceries',
                'confidence' => 0.90
            ]);

        $result1 = $this->service->parseFinanceText($text, $this->user->id);
        
        // Second call should use cache
        $result2 = $this->service->parseFinanceText($text, $this->user->id);

        $this->assertEquals($result1, $result2);
    }

    /** @test */
    public function it_handles_decimal_amounts()
    {
        $this->mockGeminiResponse([
            'type' => 'expense',
            'amount' => 4.75,
            'currency' => 'USD',
            'category' => 'food',
            'date' => now()->format('Y-m-d'),
            'confidence' => 0.90
        ]);

        $result = $this->service->parseFinanceText(
            "Coffee cost 4.75 dollars",
            $this->user->id
        );

        $this->assertEquals(4.75, $result['amount']);
    }

    /** @test */
    public function it_rejects_zero_or_negative_amounts()
    {
        $invalidAmounts = ['0', '-500', 'negative 100'];

        foreach ($invalidAmounts as $amount) {
            $this->mockGeminiResponse([
                'type' => 'expense',
                'amount' => 0,
                'currency' => 'BDT',
                'category' => 'unknown',
                'date' => now()->format('Y-m-d'),
                'confidence' => 0.20,
                'error' => 'Invalid amount'
            ]);

            $result = $this->service->parseFinanceText(
                "Spent {$amount} taka",
                $this->user->id
            );

            $this->assertLessThan(0.5, $result['confidence']);
        }
    }

    /**
     * Helper to mock Gemini API response
     */
    protected function mockGeminiResponse(array $data): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                ['text' => json_encode($data)]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);
    }
}
