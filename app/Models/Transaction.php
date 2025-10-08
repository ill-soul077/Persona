<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Transaction Model
 * 
 * Represents all financial activities (income and expenses).
 * Uses polymorphic relationship to link to IncomeSource or ExpenseCategory.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property float $amount
 * @property string $currency
 * @property \Illuminate\Support\Carbon $date
 * @property int|null $category_id
 * @property string|null $category_type
 * @property string|null $description
 * @property array|null $meta
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class Transaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'currency',
        'date',
        'category_id',
        'category_type',
        'description',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
        'meta' => 'array',
    ];

    /**
     * Get the user that owns the transaction.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the category (polymorphic: IncomeSource or ExpenseCategory).
     */
    public function category(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to filter income transactions.
     */
    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    /**
     * Scope to filter expense transactions.
     */
    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    /**
     * Scope to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    /**
     * Scope to order by most recent first.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc');
    }

    /**
     * Check if this is an income transaction.
     */
    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    /**
     * Check if this is an expense transaction.
     */
    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    /**
     * Get formatted amount with currency.
     */
    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' ' . number_format($this->amount, 2);
    }

    /**
     * Get vendor from meta data.
     */
    public function getVendorAttribute(): ?string
    {
        return $this->meta['vendor'] ?? null;
    }

    /**
     * Get location from meta data.
     */
    public function getLocationAttribute(): ?string
    {
        return $this->meta['location'] ?? null;
    }
}
