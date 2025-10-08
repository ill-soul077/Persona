<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Task Model
 * 
 * Represents daily tasks with support for status tracking,
 * priority levels, recurrence patterns, and completion tracking.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $due_date
 * @property string $status
 * @property string $priority
 * @property string $recurrence_type
 * @property int $recurrence_interval
 * @property \Illuminate\Support\Carbon|null $recurrence_end_date
 * @property \Illuminate\Support\Carbon|null $next_occurrence
 * @property array|null $tags
 * @property bool $created_via_ai
 * @property string|null $ai_raw_input
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Task extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'status',
        'priority',
        'recurrence_type',
        'recurrence_interval',
        'recurrence_end_date',
        'next_occurrence',
        'tags',
        'created_via_ai',
        'ai_raw_input',
        'completed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
        'recurrence_end_date' => 'datetime',
        'next_occurrence' => 'datetime',
        'tags' => 'array',
        'created_via_ai' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['is_overdue', 'is_today', 'is_upcoming'];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the history for the task.
     */
    public function history(): HasMany
    {
        return $this->hasMany(TaskHistory::class);
    }

    /**
     * Get the reminders for the task.
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(TaskReminder::class);
    }

    /**
     * Check if task is overdue.
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'pending' 
            && $this->due_date 
            && $this->due_date->isPast();
    }

    /**
     * Check if task is due today.
     */
    public function getIsTodayAttribute(): bool
    {
        return $this->due_date && $this->due_date->isToday();
    }

    /**
     * Check if task is upcoming (next 7 days).
     */
    public function getIsUpcomingAttribute(): bool
    {
        return $this->due_date 
            && $this->due_date->isFuture() 
            && $this->due_date->diffInDays(now()) <= 7;
    }

    /**
     * Scope to filter pending tasks.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to filter in-progress tasks.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to filter completed tasks.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to filter overdue tasks.
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('due_date', '<', now());
    }

    /**
     * Scope to filter tasks due today.
     */
    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', today());
    }

    /**
     * Scope to order by priority (high to low).
     */
    public function scopeByPriority($query)
    {
        return $query->orderByRaw("FIELD(priority, 'high', 'medium', 'low')");
    }

    /**
     * Mark task as completed.
     */
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        $this->logHistory('completed', ['completed_at' => now()]);

        // Handle recurring tasks
        if ($this->recurrence_type !== 'none') {
            $this->createNextOccurrence();
        }
    }

    /**
     * Mark task as pending.
     */
    public function markAsPending(): void
    {
        $this->update([
            'status' => 'pending',
            'completed_at' => null,
        ]);

        $this->logHistory('uncompleted', ['completed_at' => null]);
    }

    /**
     * Create next occurrence for recurring task.
     */
    public function createNextOccurrence(): void
    {
        if ($this->recurrence_type === 'none') {
            return;
        }

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

    /**
     * Calculate next occurrence date.
     */
    public function calculateNextOccurrence($fromDate = null): \Carbon\Carbon
    {
        $baseDate = $fromDate ?? $this->due_date ?? now();

        return match($this->recurrence_type) {
            'daily' => $baseDate->copy()->addDays($this->recurrence_interval),
            'weekly' => $baseDate->copy()->addWeeks($this->recurrence_interval),
            'monthly' => $baseDate->copy()->addMonths($this->recurrence_interval),
            default => $baseDate,
        };
    }

    /**
     * Log task history.
     */
    public function logHistory(string $action, array $changes = []): void
    {
        TaskHistory::create([
            'task_id' => $this->id,
            'user_id' => $this->user_id,
            'action' => $action,
            'changes' => json_encode($changes),
        ]);
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->is_overdue;
    }

    /**
     * Check if task is recurring.
     */
    public function isRecurring(): bool
    {
        return $this->recurrence_type !== 'none';
    }

    /**
     * Scope: Tasks due in next N days.
     */
    public function scopeDueInDays($query, int $days = 7)
    {
        return $query->whereBetween('due_date', [
            now()->startOfDay(),
            now()->addDays($days)->endOfDay()
        ]);
    }

    /**
     * Scope: Filter by priority.
     */
    public function scopePriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    /**
     * Scope: Filter by tag.
     */
    public function scopeWithTag($query, string $tag)
    {
        return $query->whereJsonContains('tags', $tag);
    }
}
