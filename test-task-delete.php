<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\User;

// Get first user
$user = User::first();
if (!$user) {
    echo "❌ No users found.\n";
    exit(1);
}

echo "✓ Testing with user: {$user->name} (ID: {$user->id})\n\n";

// Get a task to delete
$task = Task::where('user_id', $user->id)->latest()->first();
if (!$task) {
    echo "❌ No tasks found to delete.\n";
    exit(1);
}

echo "✓ Found task to delete:\n";
echo "  - ID: {$task->id}\n";
echo "  - Title: {$task->title}\n";
echo "  - Priority: {$task->priority}\n";
echo "  - Status: {$task->status}\n\n";

// Count tasks before
$tasksBefore = Task::where('user_id', $user->id)->count();
echo "✓ Tasks before deletion: {$tasksBefore}\n";

// Delete the task
try {
    echo "\n🗑️  Deleting task...\n";
    $task->delete();
    
    // Count tasks after
    $tasksAfter = Task::where('user_id', $user->id)->count();
    
    echo "\n✅ SUCCESS!\n";
    echo "✓ Tasks after deletion: {$tasksAfter}\n";
    echo "✓ Tasks deleted: " . ($tasksBefore - $tasksAfter) . "\n";
    
    // Verify soft delete
    $softDeleted = Task::withTrashed()->find($task->id);
    if ($softDeleted && $softDeleted->trashed()) {
        echo "✓ Task soft deleted correctly (deleted_at: {$softDeleted->deleted_at})\n";
    }
    
    echo "\n🎉 Task deletion working correctly!\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
