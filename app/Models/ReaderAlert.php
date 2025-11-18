<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderAlert extends Model
{
    protected $fillable = [
        'alertable_type',
        'alertable_id',
        'alert_type',
        'message',
        'metadata',
        'severity',
        'acknowledged',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'acknowledged' => 'boolean',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    /**
     * Polymorphic relationship to either RoomReader or GlobalReader
     */
    public function alertable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who acknowledged this alert
     */
    public function acknowledgedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    /**
     * Acknowledge this alert
     */
    public function acknowledge(): void
    {
        $this->update([
            'acknowledged' => true,
            'acknowledged_at' => now(),
            'acknowledged_by' => auth()->id(),
        ]);
    }

    /**
     * Resolve this alert
     */
    public function resolve(): void
    {
        $this->update(['resolved_at' => now()]);
    }

    /**
     * Get unresolved alerts
     */
    public static function unresolved()
    {
        return static::whereNull('resolved_at');
    }

    /**
     * Get unacknowledged alerts
     */
    public static function unacknowledged()
    {
        return static::where('acknowledged', false);
    }
}
