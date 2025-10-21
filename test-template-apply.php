<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TaskTemplate;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

// Get first user
$user = User::first();
if (!$user) {
    echo "❌ No users found. Please create a user first.\n";
    exit(1);
}

echo "✓ Testing with user: {$user->name} (ID: {$user->id})\n\n";

// Get first template
$template = TaskTemplate::first();
if (!$template) {
    echo "❌ No templates found. Please run: php artisan db:seed --class=TaskTemplateSeeder\n";
    exit(1);
}

echo "✓ Testing template: {$template->name}\n";
echo "  - Tasks in template: " . count($template->tasks) . "\n";
echo "  - Category: {$template->category}\n\n";

// Count tasks before
$tasksBefore = Task::where('user_id', $user->id)->count();
echo "✓ Tasks before applying template: {$tasksBefore}\n";

// Test variable substitution
function substituteVariables($text) {
    $replacements = [
        '{date}' => now()->format('Y-m-d'),
        '{time}' => now()->format('H:i'),
        '{week}' => 'Week ' . now()->weekOfYear,
        '{month}' => now()->format('F'),
        '{day}' => now()->format('l'),
        '{year}' => now()->format('Y'),
    ];
    
    return str_replace(array_keys($replacements), array_values($replacements), $text);
}

// Create tasks from template
try {
    echo "\n📝 Creating tasks from template...\n";
    
    $baseDate = now();
    $createdTasks = [];
    
    foreach ($template->tasks as $taskData) {
        echo "  - Creating task: {$taskData['title']}\n";
        
        $taskAttributes = [
            'user_id' => $user->id,
            'title' => substituteVariables($taskData['title']),
            'description' => isset($taskData['description']) 
                ? substituteVariables($taskData['description']) 
                : null,
            'priority' => $taskData['priority'],
            'status' => 'pending',
        ];

        // Calculate due date based on offset
        if (isset($taskData['due_offset']) && $taskData['due_offset'] !== null) {
            $taskAttributes['due_date'] = Carbon::parse($baseDate)
                ->addDays($taskData['due_offset']);
        }

        $task = Task::create($taskAttributes);
        $createdTasks[] = $task;
        
        echo "    ✓ Created with priority: {$task->priority}, ID: {$task->id}\n";
    }
    
    // Count tasks after
    $tasksAfter = Task::where('user_id', $user->id)->count();
    $created = $tasksAfter - $tasksBefore;
    
    echo "\n✅ SUCCESS!\n";
    echo "✓ Tasks after applying template: {$tasksAfter}\n";
    echo "✓ New tasks created: {$created}\n";
    echo "✓ Expected: " . count($template->tasks) . "\n";
    
    if ($created === count($template->tasks)) {
        echo "\n🎉 Template application working correctly!\n";
    } else {
        echo "\n⚠️  Task count mismatch!\n";
    }
    
    // Show created tasks
    echo "\n📋 Created tasks:\n";
    foreach ($createdTasks as $task) {
        echo "  - [{$task->priority}] {$task->title}\n";
    }
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
