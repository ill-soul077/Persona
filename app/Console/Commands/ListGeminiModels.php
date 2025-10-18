<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ListGeminiModels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gemini:models {--filter=} {--json}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'List available Gemini models using the configured API key';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $apiKey = (string) config('services.gemini.api_key', env('GEMINI_API_KEY'));
        $baseUrl = rtrim((string) config('services.gemini.base_url', 'https://generativelanguage.googleapis.com/v1'), '/');

        if (empty($apiKey)) {
            $this->error('GEMINI_API_KEY is not set. Please configure it in your .env or services.php');
            return self::FAILURE;
        }

        $url = $baseUrl . '/models';
        $this->info('Fetching models from: ' . $url);

        try {
            $resp = Http::timeout(15)->get($url, ['key' => $apiKey]);

            if (!$resp->successful()) {
                $this->error('Failed to fetch models: HTTP '.$resp->status());
                $this->line($resp->body());
                return self::FAILURE;
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
                return self::SUCCESS;
            }

            if ($models->isEmpty()) {
                $this->warn('No models returned. Your API key may not have access or the service is unavailable.');
                return self::SUCCESS;
            }

            $this->info('Available models:');
            foreach ($models as $m) {
                $name = $m['name'] ?? '';
                // Names are often like "models/gemini-1.5-flash"
                $short = str_contains($name, '/') ? explode('/', (string)$name)[1] : $name;
                $gen = implode(',', $m['supportedGenerationMethods'] ?? []);
                $tokens = $m['inputTokenLimit'] ?? '';
                $this->line(sprintf('- %s (supported: %s%s)', $short, $gen ?: 'n/a', $tokens ? ", tokens: {$tokens}" : ''));
            }

            $this->newLine();
            $this->line('Tip: Try --filter=flash or --filter=1.5 to narrow results. Use --json for full objects.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Error: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}
