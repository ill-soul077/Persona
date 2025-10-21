<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskTemplate extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'description',
        'category',
        'tasks',
        'is_public',
        'use_count',
        'icon',
        'color',
    ];

    protected $casts = [
        'tasks' => 'array',
        'is_public' => 'boolean',
        'use_count' => 'integer',
    ];

    /**
     * Get the user who owns the template
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get templates accessible by a user (their own + public)
     */
    public function scopeAccessibleBy($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('user_id', $userId)
              ->orWhere('is_public', true);
        });
    }

    /**
     * Scope to filter by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope to get most used templates
     */
    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('use_count', 'desc')->limit($limit);
    }

    /**
     * Increment use count
     */
    public function incrementUseCount()
    {
        $this->increment('use_count');
    }

    /**
     * Get category icon
     */
    public function getCategoryIcon(): string
    {
        return match($this->category) {
            'work' => '💼',
            'personal' => '🏠',
            'health' => '💪',
            'shopping' => '🛒',
            'meeting' => '🤝',
            'routine' => '🔄',
            default => '📋',
        };
    }

    /**
     * Get category color
     */
    public function getCategoryColor(): string
    {
        return match($this->category) {
            'work' => 'blue',
            'personal' => 'purple',
            'health' => 'green',
            'shopping' => 'yellow',
            'meeting' => 'red',
            'routine' => 'indigo',
            default => 'gray',
        };
    }
}
