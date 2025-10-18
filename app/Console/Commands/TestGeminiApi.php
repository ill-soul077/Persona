<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestGeminiApi extends Command
{
    protected $signature = 'gemini:test {--simple} {--budget}';
    protected $description = 'Test Gemini API with simple or budget prompt';

    public function handle(): int
    {
        $apiKey = config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $model = 'gemini-2.5-flash'; // Force use of 2.5
        $baseUrl = 'https://generativelanguage.googleapis.com/v1';

        if (empty($apiKey)) {
            $this->error('No API key found!');
            return 1;
        }

        $this->info("Testing with:");
        $this->line("  API Key: " . substr($apiKey, 0, 20) . "...");
        $this->line("  Model: {$model}");
        $this->line("  Base URL: {$baseUrl}");
        $this->newLine();

        // Test 1: Simple prompt
        if ($this->option('simple') || !$this->option('budget')) {
            $this->info('Test 1: Simple prompt...');
            $result = $this->testSimplePrompt($baseUrl, $model, $apiKey);
            if (!$result) return 1;
        }

        // Test 2: Budget prompt
        if ($this->option('budget')) {
            $this->newLine();
            $this->info('Test 2: Budget advice prompt...');
            $result = $this->testBudgetPrompt($baseUrl, $model, $apiKey);
            if (!$result) return 1;
        }

        $this->newLine();
        $this->info('✅ All tests passed!');
        return 0;
    }

    private function testSimplePrompt(string $baseUrl, string $model, string $apiKey): bool
    {
        $prompt = 'Return only this JSON: {"test": "success", "message": "API working"}';

        try {
            $url = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";
            
            $this->line("Calling: {$url}");
            
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 256,
                ],
            ]);

            if (!$response->successful()) {
                $this->error("❌ Failed: HTTP {$response->status()}");
                $this->line($response->body());
                return false;
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            $this->line("Raw response: {$text}");
            
            // Try to parse JSON
            $text = preg_replace('/```json\s*|```/i', '', trim($text));
            $parsed = json_decode($text, true);
            
            if ($parsed && isset($parsed['test'])) {
                $this->info("✅ Simple test passed!");
                $this->line("   Model returned: " . json_encode($parsed));
                return true;
            } else {
                $this->warn("⚠️  Got response but not valid JSON");
                return true; // Still counts as success - API works
            }

        } catch (\Throwable $e) {
            $this->error("❌ Exception: " . $e->getMessage());
            Log::error('Gemini test failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return false;
        }
    }

    private function testBudgetPrompt(string $baseUrl, string $model, string $apiKey): bool
    {
        $prompt = <<<PROMPT
You are a personal finance coach. Create a budget summary.

Return ONLY valid JSON (no markdown):
{
  "summary": "Brief 2 sentence overview",
  "recommendations": [{"title": "Tip 1", "detail": "Detail 1"}],
  "suggestedAllocations": [{"category": "groceries", "amount": 500, "reason": "Essential"}],
  "risks": ["Risk 1"]
}

Context: October 2025, 5000 BDT budget, 3000 spent, 2000 remaining, 15 days left.
PROMPT;

        try {
            $url = "{$baseUrl}/models/{$model}:generateContent?key={$apiKey}";
            
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    ['parts' => [['text' => $prompt]]]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 1024,
                ],
            ]);

            if (!$response->successful()) {
                $this->error("❌ Failed: HTTP {$response->status()}");
                $this->line($response->body());
                return false;
            }

            $data = $response->json();
            $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            $this->line("Raw response length: " . strlen($text) . " chars");
            $this->line("First 200 chars: " . substr($text, 0, 200));
            
            // Try to parse JSON
            $text = preg_replace('/```json\s*|```/i', '', trim($text));
            $parsed = json_decode($text, true);
            
            if ($parsed && isset($parsed['summary'])) {
                $this->info("✅ Budget test passed!");
                $this->line("   Summary: " . substr($parsed['summary'], 0, 80));
                $this->line("   Recommendations: " . count($parsed['recommendations'] ?? []));
                $this->line("   Allocations: " . count($parsed['suggestedAllocations'] ?? []));
                return true;
            } else {
                $this->error("❌ Invalid JSON response");
                $this->line("Parse error: " . json_last_error_msg());
                return false;
            }

        } catch (\Throwable $e) {
            $this->error("❌ Exception: " . $e->getMessage());
            Log::error('Gemini budget test failed', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
