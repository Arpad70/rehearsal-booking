<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $device_id
 * @property int|null $room_id
 * @property int $channel
 * @property string|null $channel_name
 * @property float|null $voltage
 * @property float|null $current
 * @property float|null $power
 * @property float|null $power_factor
 * @property float|null $energy_total
 * @property float|null $energy_today
 * @property float|null $energy_month
 * @property bool $is_on
 * @property \Carbon\Carbon|null $last_switched_at
 * @property float|null $temperature
 * @property float|null $temperature_limit
 * @property string $status
 * @property string|null $status_message
 * @property array|null $raw_data
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property Device $device
 * @property Room|null $room
 */
class PowerMonitoring extends Model
{
    protected $table = 'power_monitoring';

    protected $fillable = [
        'device_id',
        'room_id',
        'channel',
        'channel_name',
        'voltage',
        'current',
        'power',
        'power_factor',
        'energy_total',
        'energy_today',
        'energy_month',
        'is_on',
        'last_switched_at',
        'temperature',
        'temperature_limit',
        'status',
        'status_message',
        'raw_data',
    ];

    protected function casts(): array
    {
        return [
            'channel' => 'integer',
            'voltage' => 'float',
            'current' => 'float',
            'power' => 'float',
            'power_factor' => 'float',
            'energy_total' => 'float',
            'energy_today' => 'float',
            'energy_month' => 'float',
            'is_on' => 'boolean',
            'last_switched_at' => 'datetime',
            'temperature' => 'float',
            'temperature_limit' => 'float',
            'raw_data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Device relationship
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(Device::class);
    }

    /**
     * Room relationship
     */
    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    /**
     * Get all records for a device (latest first)
     */
    public static function getLatestByDevice(int $deviceId, int $limit = 100)
    {
        return self::where('device_id', $deviceId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get records for specific channel
     */
    public static function getByChannel(int $deviceId, int $channel, int $limit = 100)
    {
        return self::where('device_id', $deviceId)
            ->where('channel', $channel)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get daily energy summary
     */
    public static function getDailyEnergy(int $deviceId, int $days = 30)
    {
        return self::where('device_id', $deviceId)
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, MAX(energy_today) as energy_wh, AVG(power) as avg_power_w')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get average power consumption for time period
     */
    public static function getAveragePower(int $deviceId, int $minutes = 60): ?float
    {
        $average = self::where('device_id', $deviceId)
            ->where('created_at', '>=', now()->subMinutes($minutes))
            ->avg('power');

        return $average ? round($average, 2) : null;
    }

    /**
     * Check if device is consuming excessive power
     */
    public function isConsumingExcessivePower(float $threshold = 2000): bool
    {
        return $this->power > $threshold;
    }

    /**
     * Check if device is overheating
     */
    public function isOverheating(): bool
    {
        if (!$this->temperature || !$this->temperature_limit) {
            return false;
        }

        return $this->temperature > $this->temperature_limit * 0.9;
    }

    /**
     * Get consumption status
     */
    public function getConsumptionStatus(): string
    {
        if (!$this->power) {
            return 'offline';
        }

        if ($this->power < 10) {
            return 'standby';
        }

        if ($this->power < 500) {
            return 'low';
        }

        if ($this->power < 2000) {
            return 'normal';
        }

        return 'high';
    }

    /**
     * Format power for display
     */
    public function getFormattedPower(): string
    {
        if (!$this->power) {
            return '0 W';
        }

        if ($this->power >= 1000) {
            return round($this->power / 1000, 2) . ' kW';
        }

        return round($this->power, 0) . ' W';
    }

    /**
     * Format energy for display
     */
    public function getFormattedEnergy(): string
    {
        if (!$this->energy_total) {
            return '0 Wh';
        }

        if ($this->energy_total >= 1000000) {
            return round($this->energy_total / 1000000, 2) . ' MWh';
        }

        if ($this->energy_total >= 1000) {
            return round($this->energy_total / 1000, 2) . ' kWh';
        }

        return round($this->energy_total, 0) . ' Wh';
    }
}
