<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Income Source Model
 * 
 * Lookup table for categorizing income transactions.
 * Examples: from_home, tuition, freelance, part_time_job, investment, other
 * 
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class IncomeSource extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get all transactions using this income source.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'category_id')
            ->where('category_type', self::class);
    }

    /**
     * Scope to get only active income sources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
