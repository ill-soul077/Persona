<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// List available Gemini models using the configured API key
Artisan::command('gemini:models {--filter=} {--json}', function () {
    $apiKey = (string) config('services.gemini.api_key', env('GEMINI_API_KEY'));
    $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1'), '/');

    if (empty($apiKey)) {
        $this->error('GEMINI_API_KEY is not set. Please configure it in your .env or services.php');
        return 1;
    }

    $url = $baseUrl . '/models';
    $this->info('Fetching models from: ' . $url);

    try {
        $resp = Http::timeout(15)->get($url, ['key' => $apiKey]);

        if (!$resp->successful()) {
            $this->error('Failed to fetch models: HTTP '.$resp->status());
            $this->line($resp->body());
            return 1;
        }

        $data = $resp->json();
        $models = collect($data['models'] ?? []);

        $filter = $this->option('filter');
        if (!empty($filter)) {
            $models = $models->filter(function ($m) use ($filter) {
                $name = is_string($m['name'] ?? '') ? $m['name'] : '';
                return stripos($name, $filter) !== false;
            })->values();
        }

        if ($this->option('json')) {
            $this->line(json_encode($models->values()->all(), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES));
            return 0;
        }

        if ($models->isEmpty()) {
            $this->warn('No models returned. Your API key may not have access or the service is unavailable.');
            return 0;
        }

        $this->info('Available models:');
        foreach ($models as $m) {
            $name = $m['name'] ?? '';
            $short = str_contains($name, '/') ? explode('/', (string)$name)[1] : $name;
            $gen = implode(',', $m['supportedGenerationMethods'] ?? []);
            $tokens = $m['inputTokenLimit'] ?? '';
            $this->line(sprintf('- %s (supported: %s%s)', $short, $gen ?: 'n/a', $tokens ? ", tokens: {$tokens}" : ''));
        }

        $this->newLine();
        $this->line('Tip: Try --filter=flash or --filter=1.5 to narrow results. Use --json for full objects.');
        return 0;
    } catch (\Throwable $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }
})->purpose('List available Gemini models using the configured API key');
