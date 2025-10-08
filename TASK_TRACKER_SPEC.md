# Task Tracker Module - Technical Specification

## 📋 Executive Summary

This document provides a complete technical specification for implementing the **Task Tracker Module** with AI-powered natural language task management using Google Gemini API. The module follows the same polished UX and architecture as the existing Finance Module.

---

## 🎯 Functional Requirements

### 1. Manual Task Management (CRUD)

**Task Fields:**
- `title` (string, required, max 255 chars)
- `description` (text, optional, max 2000 chars)
- `due_date` (datetime, optional with time component)
- `status` (enum: `pending`, `completed`)
- `priority` (enum: `low`, `medium`, `high`)
- `recurrence_type` (enum: `none`, `daily`, `weekly`, `monthly`)
- `recurrence_interval` (integer, default: 1)
- `recurrence_end_date` (datetime, optional)
- `tags` (JSON array, optional)
- `created_via_ai` (boolean, tracks AI-created tasks)
- `ai_raw_input` (text, stores original AI input)

**Operations:**
1. **Quick Add**: Minimal form (title + due_date only)
2. **Full Create**: Complete form with all fields
3. **Edit**: Update any task field
4. **Delete**: Soft delete with confirmation
5. **Toggle Status**: One-click complete/uncomplete
6. **Bulk Actions**: Select multiple tasks for batch operations

---

### 2. AI-Powered Task Management

**GeminiService::parseTaskText()**

**Input Examples:**
```
"Add meeting with team tomorrow at 10am"
"Remind me to call mom on Friday at 3pm"
"Submit assignment every Monday at 9am"
"Urgent: Review code before EOD"
"Mark meeting as done"
```

**Expected JSON Output:**
```json
{
  "action": "create",
  "title": "Meeting with team",
  "description": "Team meeting scheduled",
  "due_date": "2025-10-09 10:00:00",
  "priority": "medium",
  "recurrence_type": "none",
  "confidence": 0.95
}
```

**Action Types:**
- `create`: Create new task
- `update`: Modify existing task
- `complete`: Mark task as completed
- `delete`: Delete task

**Workflow:**
1. User enters natural language input
2. Call `ChatController@parseTask` → `GeminiService::parseTaskText()`
3. Store in `ai_logs` table with status `pending_review`
4. Display preview modal with parsed fields (editable)
5. User confirms/edits → `ChatController@confirmTask`
6. Create task + update AI log to `applied`

---

### 3. Views & UI Components

#### **3.1 List View** (`resources/views/tasks/index.blade.php`)

**Features:**
- Filter tabs: Today / This Week / All / Overdue / Completed
- Quick add button (opens modal)
- Checkbox for instant complete/uncomplete
- Priority badges (color-coded)
- Tags display
- Due date with overdue highlighting
- Recurrence indicator (🔄)
- Pagination

**Stats Cards:**
- Total Tasks
- Due Today
- This Week
- Overdue
- Completed

#### **3.2 Calendar View** (`resources/views/tasks/calendar.blade.php`)

**Implementation Options:**

**Option A: FullCalendar.js** (Recommended)
```html
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<div id="calendar"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        events: '/tasks/calendar/feed',
        eventClick: function(info) {
            window.location.href = info.event.url;
        },
        dateClick: function(info) {
            showQuickAddModal(info.dateStr);
        }
    });
    calendar.render();
});
</script>
```

**Option B: Simple HTML/CSS Calendar** (Lightweight)
```php
// Generate month grid
$startOfMonth = now()->startOfMonth();
$daysInMonth = $startOfMonth->daysInMonth;
$tasks = Task::where('user_id', auth()->id())
    ->whereMonth('due_date', now()->month)
    ->get()
    ->groupBy(fn($t) => $t->due_date->format('d'));
```

**Calendar Feed Endpoint:**
```php
// Route: GET /tasks/calendar/feed
// Returns JSON array of task events
[
    {
        "id": 123,
        "title": "Team Meeting",
        "start": "2025-10-09T10:00:00",
        "end": "2025-10-09T10:00:00",
        "url": "/tasks/123",
        "backgroundColor": "#ef4444", // high priority = red
        "classNames": ["completed-task"],
        "extendedProps": {
            "description": "Weekly team sync",
            "status": "pending",
            "priority": "high",
            "tags": ["work", "meeting"]
        }
    }
]
```

#### **3.3 Task Detail Modal/Drawer**

**Components:**
- Full task information
- Edit button
- Complete/uncomplete toggle
- Delete button
- History timeline (from `task_history` table)
- Tags list
- Recurrence details
- Reminder configuration

**History Display:**
```php
@foreach($task->history as $entry)
    <div class="flex gap-3">
        <div class="text-sm text-gray-600">
            {{ $entry->created_at->diffForHumans() }}
        </div>
        <div class="text-sm font-medium">
            {{ ucfirst($entry->action) }}
        </div>
        @if($entry->changes)
            <div class="text-xs text-gray-500">
                {{ json_encode($entry->changes, JSON_PRETTY_PRINT) }}
            </div>
        @endif
    </div>
@endforeach
```

---

### 4. Recurring Tasks & Reminders

#### **Recurrence Logic**

**Database Fields:**
- `recurrence_type`: none | daily | weekly | monthly
- `recurrence_interval`: How many days/weeks/months between occurrences
- `recurrence_end_date`: When to stop creating new occurrences
- `next_occurrence`: Calculated next due date

**Implementation:**
```php
// In Task model
public function markAsCompleted(): void
{
    $this->update([
        'status' => 'completed',
        'completed_at' => now(),
    ]);

    $this->logHistory('completed', ['completed_at' => now()]);

    // Auto-create next occurrence for recurring tasks
    if ($this->recurrence_type !== 'none') {
        $this->createNextOccurrence();
    }
}

public function createNextOccurrence(): void
{
    if ($this->recurrence_type === 'none') return;

    $nextDueDate = $this->calculateNextOccurrence();

    // Check if we've reached the end date
    if ($this->recurrence_end_date && $nextDueDate->isAfter($this->recurrence_end_date)) {
        return;
    }

    // Create new task for next occurrence
    $newTask = $this->replicate();
    $newTask->due_date = $nextDueDate;
    $newTask->next_occurrence = $this->calculateNextOccurrence($nextDueDate);
    $newTask->status = 'pending';
    $newTask->completed_at = null;
    $newTask->save();
}

public function calculateNextOccurrence($fromDate = null): Carbon
{
    $baseDate = $fromDate ?? $this->due_date ?? now();

    return match($this->recurrence_type) {
        'daily' => $baseDate->copy()->addDays($this->recurrence_interval),
        'weekly' => $baseDate->copy()->addWeeks($this->recurrence_interval),
        'monthly' => $baseDate->copy()->addMonths($this->recurrence_interval),
        default => $baseDate,
    };
}
```

#### **Reminders**

**Table: `task_reminders`**
```php
Schema::create('task_reminders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('task_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->dateTime('remind_at');
    $table->boolean('is_sent')->default(false);
    $table->timestamp('sent_at')->nullable();
    $table->timestamps();
});
```

**In-App Notification UI:**
```blade
<!-- Header notification bell -->
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($pendingReminders > 0)
            <span class="absolute top-0 right-0 w-4 h-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {{ $pendingReminders }}
            </span>
        @endif
    </button>
    
    <div x-show="open" class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl">
        @foreach($reminders as $reminder)
            <div class="p-4 border-b hover:bg-gray-50">
                <a href="{{ route('tasks.show', $reminder->task) }}">
                    <div class="font-medium">{{ $reminder->task->title }}</div>
                    <div class="text-sm text-gray-600">
                        Due {{ $reminder->task->due_date->diffForHumans() }}
                    </div>
                </a>
            </div>
        @endforeach
    </div>
</div>
```

**Scheduled Job (Optional):**
```php
// app/Console/Commands/SendTaskReminders.php
class SendTaskReminders extends Command
{
    public function handle()
    {
        $reminders = TaskReminder::pending()->get();
        
        foreach ($reminders as $reminder) {
            // Send notification (email, SMS, push, etc.)
            $reminder->markAsSent();
        }
    }
}

// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('tasks:send-reminders')->everyMinute();
}
```

---

## 🗄️ Database Schema

### Tables Created

#### **1. tasks**
```sql
CREATE TABLE tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT NULL,
    due_date DATETIME NULL,
    status ENUM('pending', 'completed') DEFAULT 'pending',
    completed_at TIMESTAMP NULL,
    recurrence_type ENUM('none', 'daily', 'weekly', 'monthly') DEFAULT 'none',
    recurrence_interval INT DEFAULT 1,
    recurrence_end_date DATETIME NULL,
    next_occurrence DATETIME NULL,
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    tags JSON NULL,
    created_via_ai BOOLEAN DEFAULT FALSE,
    ai_raw_input TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    
    INDEX idx_user_status (user_id, status),
    INDEX idx_user_due_date (user_id, due_date),
    INDEX idx_next_occurrence (next_occurrence),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### **2. task_history**
```sql
CREATE TABLE task_history (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(255) NOT NULL,
    changes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_task_created (task_id, created_at),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### **3. task_reminders**
```sql
CREATE TABLE task_reminders (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    remind_at DATETIME NOT NULL,
    is_sent BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    INDEX idx_user_sent_remind (user_id, is_sent, remind_at),
    FOREIGN KEY (task_id) REFERENCES tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

### Relationships

```
users (1) ──< (many) tasks
tasks (1) ──< (many) task_history
tasks (1) ──< (many) task_reminders
```

---

## 🔧 Backend Implementation

### Controllers

#### **TaskController**

**Methods:**
- `index()` - List tasks with filters
- `create()` - Show create form
- `store()` - Save new task
- `show()` - View task details
- `edit()` - Show edit form
- `update()` - Update task
- `destroy()` - Delete task (soft delete)
- `toggleStatus()` - Complete/uncomplete task
- `quickAdd()` - Quick add via AJAX
- `calendarFeed()` - JSON feed for calendar

**Validation Rules:**
```php
[
    'title' => 'required|string|max:255',
    'description' => 'nullable|string|max:2000',
    'due_date' => 'nullable|date',
    'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
    'recurrence_type' => ['required', Rule::in(['none', 'daily', 'weekly', 'monthly'])],
    'recurrence_interval' => 'nullable|integer|min:1|max:365',
    'recurrence_end_date' => 'nullable|date|after:due_date',
    'tags' => 'nullable|string',
]
```

#### **ChatController (Extended)**

**New Methods:**
- `parseTask()` - Parse natural language task
- `confirmTask()` - Save AI-parsed task
- `updateTask()` - Update existing task via AI

**Request Flow:**
```
User Input
    ↓
parseTask() → GeminiService::parseTaskText()
    ↓
Store in ai_logs (pending_review)
    ↓
Return JSON to frontend
    ↓
User Reviews/Edits in Modal
    ↓
confirmTask() → Create Task + Update ai_log (applied)
    ↓
Return Success
```

### Services

#### **GeminiService::parseTaskText()**

**Prompt Template:**
```
You are a task parser. Extract structured task data from natural language input.

Input: "{rawText}"

Extract the following information and return ONLY valid JSON:
{
    "action": "create|update|complete|delete",
    "title": brief task title (max 50 chars),
    "description": detailed description,
    "due_date": ISO 8601 datetime or null,
    "priority": "low"|"medium"|"high",
    "recurrence_type": "none"|"daily"|"weekly"|"monthly",
    "confidence": 0.0 to 1.0
}

Rules:
- Extract action verbs for title
- Parse relative dates (tomorrow, next week, friday)
- Current datetime: {now()}
- Detect recurrence keywords (daily, weekly, every day, etc.)
- Infer priority from urgency words (urgent, asap → high)

Examples:
"Meeting with team tomorrow at 10am" → {"action":"create","title":"Meeting with team","due_date":"2025-10-09 10:00:00","priority":"medium","recurrence_type":"none","confidence":0.95}
```

---

## 🎨 UX & Design Consistency

### Visual Language (Matching Finance Module)

**Colors:**
- Primary: Purple-600 (#9333ea)
- Secondary: Indigo-600 (#4f46e5)
- Success: Green-500 (#10b981)
- Warning: Yellow-500 (#eab308)
- Danger: Red-500 (#ef4444)

**Components:**
- Rounded corners: `rounded-xl` (12px)
- Shadows: `shadow-sm` and `shadow-lg`
- Transitions: `transition-all duration-200`
- Hover states on all interactive elements

### Keyboard Shortcuts

```javascript
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + K: Quick add task
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        showQuickAddModal();
    }
    
    // Ctrl/Cmd + /: Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === '/') {
        e.preventDefault();
        document.getElementById('task-search').focus();
    }
});
```

### Mobile Responsiveness

**Breakpoints:**
- `sm:` 640px+
- `md:` 768px+
- `lg:` 1024px+
- `xl:` 1280px+

**Mobile Optimizations:**
- Stacked layout on small screens
- Bottom sheet modals instead of centered
- Touch-friendly tap targets (min 44px)
- Swipe gestures for complete/delete

---

## 🧪 Testing & QA

### Automated Tests

#### **Unit Tests** (`tests/Unit/`)

**GeminiServiceTaskParsingTest.php**
```php
public function test_parses_simple_task()
{
    $result = $this->gemini->parseTaskText("Meeting tomorrow at 10am");
    
    $this->assertEquals('create', $result['action']);
    $this->assertStringContainsString('Meeting', $result['title']);
    $this->assertNotNull($result['due_date']);
}

public function test_detects_recurrence()
{
    $result = $this->gemini->parseTaskText("Team standup every Monday at 9am");
    
    $this->assertEquals('weekly', $result['recurrence_type']);
}

public function test_infers_high_priority()
{
    $result = $this->gemini->parseTaskText("URGENT: Submit report before EOD");
    
    $this->assertEquals('high', $result['priority']);
}
```

#### **Feature Tests** (`tests/Feature/`)

**TaskChatFlowTest.php**
```php
public function test_parse_create_confirm_workflow()
{
    // Step 1: Parse
    $parseResponse = $this->postJson('/api/chat/parse-task', [
        'message' => 'Meeting tomorrow at 10am'
    ]);
    
    $parseResponse->assertOk();
    $aiLogId = $parseResponse->json('ai_log_id');
    
    // Step 2: Confirm
    $confirmResponse = $this->postJson('/api/chat/confirm-task', [
        'ai_log_id' => $aiLogId,
        'title' => 'Team Meeting',
        'due_date' => now()->addDay()->setTime(10, 0),
        'priority' => 'medium',
    ]);
    
    $confirmResponse->assertOk();
    $this->assertDatabaseHas('tasks', ['title' => 'Team Meeting']);
}

public function test_mark_task_as_done_via_ai()
{
    $task = Task::factory()->create();
    
    $response = $this->postJson('/api/chat/update-task', [
        'message' => 'Mark it as done',
        'task_id' => $task->id,
    ]);
    
    $response->assertOk();
    $this->assertEquals('completed', $task->fresh()->status);
}
```

**TaskRecurrenceTest.php**
```php
public function test_creates_next_occurrence_on_completion()
{
    $task = Task::factory()->create([
        'recurrence_type' => 'weekly',
        'due_date' => now()->addWeek(),
    ]);
    
    $task->markAsCompleted();
    
    // Check new task created
    $nextTask = Task::where('title', $task->title)
        ->where('status', 'pending')
        ->first();
    
    $this->assertNotNull($nextTask);
    $this->assertEquals(
        $task->due_date->addWeek()->format('Y-m-d'),
        $nextTask->due_date->format('Y-m-d')
    );
}
```

**CalendarRenderingTest.php**
```php
public function test_calendar_feed_returns_correct_json()
{
    Task::factory()->count(10)->create([
        'user_id' => $this->user->id,
        'due_date' => now()->addDays(rand(1, 30)),
    ]);
    
    $response = $this->getJson('/tasks/calendar/feed?start=2025-10-01&end=2025-10-31');
    
    $response->assertOk();
    $events = $response->json();
    
    $this->assertCount(10, $events);
    $this->assertArrayHasKey('id', $events[0]);
    $this->assertArrayHasKey('title', $events[0]);
    $this->assertArrayHasKey('start', $events[0]);
}
```

### UI Acceptance Tests

**Manual Test Scenarios:**

1. **Task Creation (Manual)**
   - Navigate to Tasks
   - Click "New Task"
   - Fill all fields
   - Verify task appears in list
   - Check database entry

2. **Task Creation (AI)**
   - Enter: "Meeting tomorrow at 10am"
   - Verify preview modal shows parsed data
   - Edit title if needed
   - Confirm
   - Verify task created

3. **Quick Complete**
   - Click checkbox on task
   - Verify status changes to completed
   - Verify completed_at timestamp set
   - Check history log

4. **Recurring Task Flow**
   - Create weekly recurring task
   - Complete it
   - Verify next occurrence created
   - Check due_date is 1 week later

5. **Calendar View**
   - Switch to calendar view
   - Verify tasks show on correct dates
   - Click date → quick add modal
   - Click task → details page

6. **Mobile Responsiveness**
   - Test on viewport: 375px (iPhone)
   - Verify touch targets
   - Check swipe gestures
   - Validate modal layouts

---

## 🚀 Deployment Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Register TaskPolicy in `AuthServiceProvider`
- [ ] Clear route cache: `php artisan route:clear`
- [ ] Test Gemini API key in production
- [ ] Configure task reminder scheduled job
- [ ] Set up notification channels (email/SMS)
- [ ] Run test suite: `php artisan test`
- [ ] Check mobile responsiveness
- [ ] Performance test with 1000+ tasks
- [ ] Set up error monitoring (Sentry/Bugsnag)

---

## 📈 Future Enhancements (v2.0)

1. **Subtasks** - Break down tasks into smaller steps
2. **Task Dependencies** - "Task B can't start until Task A is done"
3. **Collaborative Tasks** - Assign tasks to other users
4. **File Attachments** - Upload files to tasks
5. **Time Tracking** - Log hours spent on tasks
6. **Kanban Board** - Drag-and-drop task management
7. **Email Integration** - Create tasks from emails
8. **Voice Input** - Use speech-to-text for task creation
9. **Smart Suggestions** - AI suggests tasks based on patterns
10. **Productivity Analytics** - Completion rates, time trends

---

## 📝 Summary

This specification provides everything needed to implement a production-ready Task Tracker module with:

✅ **Complete CRUD** operations with advanced filtering  
✅ **AI-powered parsing** using Gemini API  
✅ **Recurring tasks** with automatic next occurrence  
✅ **Calendar integration** with FullCalendar.js  
✅ **In-app reminders** with notification system  
✅ **Mobile-responsive** design with touch gestures  
✅ **Comprehensive testing** (unit, feature, UI)  
✅ **Same polished UX** as Finance Module  

**Estimated Development Time:** 20-30 hours  
**Lines of Code:** ~3,500 (backend + frontend)  
**Files Created:** 15+ (migrations, models, controllers, views, tests)

---

**Last Updated:** October 8, 2025  
**Version:** 1.0  
**Author:** Persona Development Team
