<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'room_id',
        'user_id',
        'location',
        'action',
        'result',
        'access_granted',
        'failure_reason',
        'ip',
        'access_code',
        'access_type',
        'reader_type',
        'global_reader_id',
        'ip_address',
        'user_agent',
        'validated_at',
    ];

    protected $casts = [
        'access_granted' => 'boolean',
        'validated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationship: Log belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withDefault();
    }

    /**
     * Relationship: Log belongs to Room
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class)->withDefault();
    }

    /**
     * Relationship: Log belongs to GlobalReader
     */
    public function globalReader(): BelongsTo
    {
        return $this->belongsTo(GlobalReader::class)->withDefault();
    }

    /**
     * Check if validation was successful
     */
    public function wasSuccessful(): bool
    {
        return $this->access_granted === true;
    }

    /**
     * Get reason for validation failure
     */
    public function getFailureReason(): ?string
    {
        if ($this->wasSuccessful()) {
            return null;
        }

        return $this->failure_reason ?? 'unknown';
    }
}