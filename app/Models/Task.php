<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
 * @property string|null $recurrence
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Task extends Model
{
    use HasFactory;

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
        'recurrence',
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
    ];

    /**
     * Get the user that owns the task.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
    }

    /**
     * Check if task is overdue.
     */
    public function isOverdue(): bool
    {
        return $this->due_date && 
               $this->due_date->isPast() && 
               $this->status !== 'completed';
    }

    /**
     * Check if task is recurring.
     */
    public function isRecurring(): bool
    {
        return !is_null($this->recurrence);
    }
}
