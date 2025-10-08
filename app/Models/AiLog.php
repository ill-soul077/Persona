<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AI Log Model
 * 
 * Audit trail for all AI chatbot interactions.
 * Stores raw inputs, parsed outputs, model metadata, and processing status.
 * Critical for model training, debugging, and compliance.
 * 
 * @property int $id
 * @property int $user_id
 * @property string $module
 * @property string $raw_text
 * @property array|null $parsed_json
 * @property string $model
 * @property float|null $confidence
 * @property string $status
 * @property string|null $error_message
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class AiLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'module',
        'raw_text',
        'parsed_json',
        'model',
        'confidence',
        'status',
        'error_message',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'parsed_json' => 'array',
        'confidence' => 'decimal:4',
    ];

    /**
     * Get the user that owns the log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to filter finance module logs.
     */
    public function scopeFinance($query)
    {
        return $query->where('module', 'finance');
    }

    /**
     * Scope to filter task module logs.
     */
    public function scopeTasks($query)
    {
        return $query->where('module', 'tasks');
    }

    /**
     * Scope to filter logs needing review.
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    /**
     * Scope to filter failed logs.
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope to filter successfully applied logs.
     */
    public function scopeApplied($query)
    {
        return $query->where('status', 'applied');
    }

    /**
     * Mark log as applied (transaction/task created).
     */
    public function markAsApplied(): void
    {
        $this->update(['status' => 'applied']);
    }

    /**
     * Mark log as failed with error message.
     */
    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
