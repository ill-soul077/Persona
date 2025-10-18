<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$service = new \App\Services\GeminiService();

$context = [
    'budget' => 5000,
    'total_spent' => 3500,
    'total_income' => 6000,
    'categories' => [
        ['name' => 'Food', 'spent' => 1500, 'budget' => 2000],
        ['name' => 'Transport', 'spent' => 800, 'budget' => 1000],
        ['name' => 'Entertainment', 'spent' => 1200, 'budget' => 1500],
    ]
];

echo "Testing Heuristic Budget Advice...\n\n";
$result = $service->generateHeuristicBudgetAdvice($context);

echo "Summary: " . $result['summary'] . "\n\n";
echo "Recommendations:\n";
foreach ($result['recommendations'] as $rec) {
    echo "- " . $rec . "\n";
}

echo "\n\nFull Result:\n";
print_r($result);
