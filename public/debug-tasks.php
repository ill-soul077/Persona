<!DOCTYPE html>
<html>
<head>
    <title>Task Debug</title>
</head>
<body>
    <h1>Task Debug Information</h1>
    
    <?php
    use App\Models\Task;
    
    echo "<h2>Database Connection Test:</h2>";
    try {
        $taskCount = Task::count();
        echo "<p>✅ Database connected successfully</p>";
        echo "<p>Total tasks in database: {$taskCount}</p>";
        
        echo "<h2>Recent Tasks:</h2>";
        $tasks = Task::latest()->take(5)->get();
        
        if ($tasks->count() > 0) {
            echo "<ul>";
            foreach ($tasks as $task) {
                echo "<li>ID: {$task->id} - {$task->title} (Status: {$task->status}, User: {$task->user_id})</li>";
            }
            echo "</ul>";
        } else {
            echo "<p>❌ No tasks found in database</p>";
        }
        
        echo "<h2>Create Test Task:</h2>";
        $newTask = Task::create([
            'user_id' => 1,
            'title' => 'Debug Test Task ' . now()->format('H:i:s'),
            'description' => 'Test task created from debug page',
            'priority' => 'medium',
            'status' => 'pending',
            'recurrence_type' => 'none',
            'recurrence_interval' => 1
        ]);
        echo "<p>✅ Test task created with ID: {$newTask->id}</p>";
        
    } catch (Exception $e) {
        echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <p><a href="/tasks">Go to Tasks Page</a></p>
</body>
</html>