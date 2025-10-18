<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetSummary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'summary_data',
        'model_used',
        'is_fallback',
    ];

    protected $casts = [
        'summary_data' => 'array',
        'is_fallback' => 'boolean',
        'month' => 'date',
    ];

    /**
     * Get the user that owns this summary.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this summary is still fresh (within 24 hours).
     */
    public function isFresh(): bool
    {
        return $this->updated_at->diffInHours(now()) < 24;
    }

    /**
     * Scope to find summary for a specific month.
     */
    public function scopeForMonth($query, int $year, int $month)
    {
        $monthDate = sprintf('%04d-%02d-01', $year, $month);
        return $query->where('month', $monthDate);
    }
}
