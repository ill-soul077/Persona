<?php

namespace App\Http\Controllers;

use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TestController extends Controller
{
    public function testGeminiApi()
    {
        try {
            // Test direct API call
            $apiKey = 'AIzaSyBmX9e8OozSX8NAWwOa8094OM-9eNZGY-8';
            $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=" . $apiKey;
            
            $response = Http::timeout(30)->post($url, [
                'contents' => [
                    [
                        'parts' => [
                            [
                                'text' => 'Hello, can you respond with a simple greeting?'
                            ]
                        ]
                    ]
                ]
            ]);

            $result = [
                'status' => $response->status(),
                'success' => $response->successful(),
                'body' => $response->json(),
                'api_key_valid' => $response->successful()
            ];

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                    $result['ai_response'] = $data['candidates'][0]['content']['parts'][0]['text'];
                }
            }

            return response()->json($result);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'api_key_valid' => false
            ]);
        }
    }

    public function testGeminiService()
    {
        try {
            $service = new GeminiService();
            $response = $service->parseFinanceText('Hello, this is a test message');
            
            return response()->json([
                'success' => true,
                'service_response' => $response
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}