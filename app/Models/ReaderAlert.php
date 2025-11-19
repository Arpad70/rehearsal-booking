<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReaderAlert extends Model
{
    protected $fillable = [
        'room_reader_id',
        'global_reader_id',
        'reader_type',
        'alert_type',
        'message',
        'severity',
        'resolved',
        'resolution_notes',
        'resolved_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    /**
     * Relationship: Alert belongs to RoomReader
     */
    public function roomReader(): BelongsTo
    {
        return $this->belongsTo(RoomReader::class)->withDefault();
    }

    /**
     * Relationship: Alert belongs to GlobalReader
     */
    public function globalReader(): BelongsTo
    {
        return $this->belongsTo(GlobalReader::class)->withDefault();
    }

    /**
     * Mark as resolved
     */
    public function markResolved(?string $notes = null): void
    {
        $this->update([
            'resolved' => true,
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }

    /**
     * Get unresolved alerts
     */
    public static function unresolved()
    {
        return static::where('resolved', false);
    }

    /**
     * Get critical alerts
     */
    public static function critical()
    {
        return static::where('severity', 'critical')->where('resolved', false);
    }
}
