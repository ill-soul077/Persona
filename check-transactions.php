<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Transaction Count: " . App\Models\Transaction::count() . "\n";
echo "Income Total: $" . App\Models\Transaction::where('type', 'income')->sum('amount') . "\n";
echo "Expense Total: $" . App\Models\Transaction::where('type', 'expense')->sum('amount') . "\n";

if (App\Models\Transaction::count() > 0) {
    echo "\nSample transactions:\n";
    foreach (App\Models\Transaction::latest()->take(3)->get() as $t) {
        echo "- {$t->type}: \${$t->amount} on {$t->date} - {$t->description}\n";
    }
}
