<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Simulate what happens in the controller
$user = \App\Models\User::first();
if (!$user) {
    echo "No users found. Run seeders first.\n";
    exit(1);
}

echo "Testing budget AI generation for user: {$user->email}\n\n";

$context = [
    'month_name' => now()->format('F Y'),
    'budget_amount' => 5000,
    'total_spent' => 3500,
    'remaining' => 1500,
    'days_left' => 15,
    'currency' => 'BDT',
    'category_breakdown' => [
        ['name' => 'Food', 'spent' => 1500],
        ['name' => 'Transport', 'spent' => 800],
        ['name' => 'Entertainment', 'spent' => 1200],
    ]
];

echo "Context:\n";
print_r($context);
echo "\n\nCalling GeminiService->generateBudgetAdvice()...\n\n";

$service = new \App\Services\GeminiService();

try {
    $result = $service->generateBudgetAdvice($context);
    
    echo "✅ SUCCESS!\n\n";
    echo "Summary: " . $result['summary'] . "\n\n";
    echo "Is Fallback: " . ($result['fallback'] ?? false ? 'YES' : 'NO') . "\n\n";
    echo "Recommendations:\n";
    foreach ($result['recommendations'] as $rec) {
        if (is_array($rec)) {
            echo "- {$rec['title']}: {$rec['detail']}\n";
        } else {
            echo "- $rec\n";
        }
    }
    
} catch (\Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Code: " . $e->getCode() . "\n";
    echo "\nThis should have been caught and returned heuristic fallback!\n";
}
