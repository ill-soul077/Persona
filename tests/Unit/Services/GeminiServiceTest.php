<?php

namespace Tests\Unit\Services;

use App\Services\GeminiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Gemini Service Unit Tests
 * 
 * Tests the GeminiService's ability to parse natural language input
 * and handle API failures gracefully with fallback mechanisms.
 */
class GeminiServiceTest extends TestCase
{
    protected GeminiService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GeminiService();
        Cache::flush();
    }

    /**
     * Test parsing simple expense text
     */
    public function test_parses_simple_expense_text(): void
    {
        // Mock Gemini API response
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 20.00,
                                        'category' => 'fast_food',
                                        'description' => 'pizza',
                                        'meta' => ['vendor' => 'Pizza Hut'],
                                        'confidence' => 0.95,
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->service->parseFinanceText('spent 20 on pizza');

        $this->assertEquals('expense', $result['type']);
        $this->assertEquals(20.00, $result['amount']);
        $this->assertEquals('fast_food', $result['category']);
        $this->assertGreaterThan(0.9, $result['confidence']);
    }

    /**
     * Test fallback mechanism on API failure
     */
    public function test_fallback_on_api_failure(): void
    {
        // Mock API failure
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $result = $this->service->parseFinanceText('spent 20 on pizza');

        // Should return fallback data
        $this->assertEquals('expense', $result['type']);
        $this->assertEquals(0.00, $result['amount']);
        $this->assertEquals('other', $result['category']);
        $this->assertEquals(0.0, $result['confidence']);
        $this->assertArrayHasKey('error', $result);
    }

    /**
     * Test parsing income text
     */
    public function test_parses_income_text(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'income',
                                        'amount' => 500.00,
                                        'category' => 'from_home',
                                        'description' => 'monthly allowance',
                                        'meta' => [],
                                        'confidence' => 0.98,
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->service->parseFinanceText('received 500 from home');

        $this->assertEquals('income', $result['type']);
        $this->assertEquals(500.00, $result['amount']);
        $this->assertEquals('from_home', $result['category']);
    }

    /**
     * Test parsing task text
     */
    public function test_parses_task_text(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'title' => 'Call mom',
                                        'description' => 'remind me to call mom tomorrow at 3pm',
                                        'due_date' => now()->addDay()->setTime(15, 0)->toIso8601String(),
                                        'priority' => 'medium',
                                        'recurrence' => null,
                                        'confidence' => 0.92,
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        $result = $this->service->parseTaskText('remind me to call mom tomorrow at 3pm');

        $this->assertEquals('Call mom', $result['title']);
        $this->assertEquals('medium', $result['priority']);
        $this->assertNotNull($result['due_date']);
    }

    /**
     * Test caching mechanism
     */
    public function test_caches_parsed_results(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [
                    [
                        'content' => [
                            'parts' => [
                                [
                                    'text' => json_encode([
                                        'type' => 'expense',
                                        'amount' => 15.00,
                                        'category' => 'coffee_snacks',
                                        'description' => 'coffee',
                                        'meta' => [],
                                        'confidence' => 0.94,
                                    ])
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200)
        ]);

        // First call - should hit API
        $result1 = $this->service->parseFinanceText('spent 15 on coffee');
        
        // Second call with same text - should hit cache
        $result2 = $this->service->parseFinanceText('spent 15 on coffee');

        $this->assertEquals($result1, $result2);
        
        // Only one API call should have been made
        Http::assertSentCount(1);
    }

    /**
     * Test health check functionality
     */
    public function test_health_check_returns_true_on_success(): void
    {
        Http::fake([
            '*/models*' => Http::response(['models' => []], 200)
        ]);

        $isHealthy = $this->service->healthCheck();

        $this->assertTrue($isHealthy);
    }

    /**
     * Test health check returns false on failure
     */
    public function test_health_check_returns_false_on_failure(): void
    {
        Http::fake([
            '*' => Http::response([], 500)
        ]);

        $isHealthy = $this->service->healthCheck();

        $this->assertFalse($isHealthy);
    }
}
