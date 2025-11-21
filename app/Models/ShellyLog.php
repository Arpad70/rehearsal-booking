<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShellyLog extends Model
{
    protected $fillable = [
        'device_id',
        'room_id',
        'lights_power',
        'lights_energy',
        'lights_voltage',
        'lights_current',
        'outlets_power',
        'outlets_energy',
        'outlets_voltage',
        'outlets_current',
        'total_power',
        'total_energy',
        'cost',
        'measured_at',
    ];

    protected function casts(): array
    {
        return [
            'lights_power' => 'decimal:3',
            'lights_energy' => 'decimal:6',
            'lights_voltage' => 'decimal:2',
            'lights_current' => 'decimal:3',
            'outlets_power' => 'decimal:3',
            'outlets_energy' => 'decimal:6',
            'outlets_voltage' => 'decimal:2',
            'outlets_current' => 'decimal:3',
            'total_power' => 'decimal:3',
            'total_energy' => 'decimal:6',
            'cost' => 'decimal:2',
            'measured_at' => 'datetime',
        ];
    }

    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Výpočet nákladů na elektřinu
     */
    public function calculateCost(float $pricePerKwh = 5.5): float
    {
        return round($this->energy * $pricePerKwh, 2);
    }

    /**
     * Scope pro dnes
     */
    public function scopeToday($query)
    {
        return $query->whereDate('measured_at', today());
    }

    /**
     * Scope pro tento týden
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('measured_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope pro tento měsíc
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('measured_at', now()->month)
            ->whereYear('measured_at', now()->year);
    }
}
