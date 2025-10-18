<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

/**
 * Budget Model
 * 
 * Represents monthly budget limits set by users.
 * Tracks spending goals and provides budget vs actual comparisons.
 * 
 * @property int $id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon $month
 * @property float $amount
 * @property string $currency
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Budget extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'month',
        'amount',
        'currency',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'month' => 'date',
        'amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the budget.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get total expenses for this budget month.
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->user->transactions()
            ->where('type', 'expense')
            ->whereYear('date', $this->month->year)
            ->whereMonth('date', $this->month->month)
            ->sum('amount');
    }

    /**
     * Get remaining budget amount.
     */
    public function getRemainingAttribute(): float
    {
        return $this->amount - $this->total_spent;
    }

    /**
     * Get percentage of budget used.
     */
    public function getPercentageUsedAttribute(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }
        
        return min(($this->total_spent / $this->amount) * 100, 100);
    }

    /**
     * Check if budget is exceeded.
     */
    public function isExceeded(): bool
    {
        return $this->total_spent > $this->amount;
    }

    /**
     * Check if budget is near limit (>= 80%).
     */
    public function isNearLimit(): bool
    {
        return $this->percentage_used >= 80 && !$this->isExceeded();
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        if ($this->isExceeded()) {
            return 'red';
        } elseif ($this->isNearLimit()) {
            return 'yellow';
        } else {
            return 'green';
        }
    }

    /**
     * Scope to get budget for a specific month.
     */
    public function scopeForMonth($query, $year, $month)
    {
        return $query->whereYear('month', $year)
                     ->whereMonth('month', $month);
    }

    /**
     * Scope to get current month's budget.
     */
    public function scopeCurrentMonth($query)
    {
        return $query->forMonth(now()->year, now()->month);
    }

    /**
     * Get formatted month name.
     */
    public function getMonthNameAttribute(): string
    {
        return $this->month->format('F Y');
    }
}
