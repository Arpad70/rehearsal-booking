<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceHealthCheck extends Model
{
    protected $fillable = [
        'device_id',
        'status',
        'response_time_ms',
        'diagnostics',
        'error_message',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'diagnostics' => 'array',
            'checked_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Scope pro online zařízení
     */
    public function scopeOnline($query)
    {
        return $query->where('status', 'online');
    }

    /**
     * Scope pro offline zařízení
     */
    public function scopeOffline($query)
    {
        return $query->where('status', 'offline');
    }

    /**
     * Scope pro poslední check v určitém časovém období
     */
    public function scopeRecent($query, int $minutes = 5)
    {
        return $query->where('checked_at', '>=', now()->subMinutes($minutes));
    }
}
