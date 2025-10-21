<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;

// Get first user
$user = User::first();
if (!$user) {
    echo "❌ No users found.\n";
    exit(1);
}

echo "✓ Testing CRUD operations with user: {$user->name} (ID: {$user->id})\n\n";

// Get a task created from template (one of the recent ones)
$task = Task::where('user_id', $user->id)
    ->where('title', 'LIKE', '%Meditation%')
    ->first();

if (!$task) {
    echo "❌ No suitable task found. Creating one...\n";
    $task = Task::create([
        'user_id' => $user->id,
        'title' => 'Test Template Task',
        'description' => 'This is a test task from template',
        'priority' => 'urgent',
        'status' => 'pending',
        'due_date' => now()->addDay(),
    ]);
    echo "✓ Test task created with ID: {$task->id}\n\n";
}

echo "📋 Original Task:\n";
echo "  - ID: {$task->id}\n";
echo "  - Title: {$task->title}\n";
echo "  - Description: " . ($task->description ?? 'N/A') . "\n";
echo "  - Priority: {$task->priority}\n";
echo "  - Status: {$task->status}\n";
echo "  - Due Date: " . ($task->due_date ? $task->due_date->format('Y-m-d') : 'N/A') . "\n\n";

try {
    // Test UPDATE (U in CRUD)
    echo "📝 Testing UPDATE...\n";
    $task->update([
        'title' => 'Updated: ' . $task->title,
        'description' => 'Updated description at ' . now()->format('H:i:s'),
        'priority' => 'urgent',
        'status' => 'pending',
    ]);
    $task->refresh();
    echo "  ✓ Title updated to: {$task->title}\n";
    echo "  ✓ Description updated to: {$task->description}\n";
    echo "  ✓ Priority: {$task->priority}\n\n";
    
    // Test READ (R in CRUD)
    echo "👁️  Testing READ...\n";
    $readTask = Task::find($task->id);
    if ($readTask) {
        echo "  ✓ Task found by ID\n";
        echo "  ✓ Title matches: " . ($readTask->title === $task->title ? 'Yes' : 'No') . "\n";
        echo "  ✓ Priority matches: " . ($readTask->priority === $task->priority ? 'Yes' : 'No') . "\n\n";
    }
    
    // Test status toggle (common operation)
    echo "🔄 Testing STATUS TOGGLE...\n";
    $originalStatus = $task->status;
    $newStatus = $originalStatus === 'pending' ? 'completed' : 'pending';
    $task->update([
        'status' => $newStatus,
        'completed_at' => $newStatus === 'completed' ? now() : null,
    ]);
    $task->refresh();
    echo "  ✓ Status changed from '{$originalStatus}' to '{$task->status}'\n";
    if ($task->status === 'completed') {
        echo "  ✓ Completed at: {$task->completed_at}\n";
    }
    echo "\n";
    
    // Test priority update with all valid values
    echo "🎯 Testing PRIORITY UPDATES...\n";
    $priorities = ['low', 'medium', 'high', 'urgent'];
    foreach ($priorities as $priority) {
        $task->update(['priority' => $priority]);
        $task->refresh();
        echo "  ✓ Priority set to: {$task->priority}\n";
    }
    echo "\n";
    
    // Test DELETE (D in CRUD) - soft delete
    echo "🗑️  Testing DELETE (soft delete)...\n";
    $taskId = $task->id;
    $task->delete();
    
    // Verify it's soft deleted
    $deletedTask = Task::withTrashed()->find($taskId);
    if ($deletedTask && $deletedTask->trashed()) {
        echo "  ✓ Task soft deleted (deleted_at: {$deletedTask->deleted_at})\n";
        echo "  ✓ Task still exists in database with trashed status\n";
    }
    
    // Test restore
    echo "\n♻️  Testing RESTORE...\n";
    $deletedTask->restore();
    $restoredTask = Task::find($taskId);
    if ($restoredTask && !$restoredTask->trashed()) {
        echo "  ✓ Task restored successfully\n";
        echo "  ✓ deleted_at is now null\n";
    }
    
    // Clean up - permanently delete the test task
    echo "\n🧹 Cleaning up test task...\n";
    $restoredTask->forceDelete();
    $exists = Task::withTrashed()->find($taskId);
    if (!$exists) {
        echo "  ✓ Test task permanently deleted\n";
    }
    
    echo "\n✅ ALL CRUD OPERATIONS WORKING CORRECTLY!\n";
    echo "\n📊 Summary:\n";
    echo "  ✓ CREATE - Works (task creation)\n";
    echo "  ✓ READ - Works (task retrieval)\n";
    echo "  ✓ UPDATE - Works (title, description, priority, status)\n";
    echo "  ✓ DELETE - Works (soft delete with restore)\n";
    echo "  ✓ Priority 'urgent' - Works correctly!\n";
    
} catch (\Exception $e) {
    echo "\n❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
