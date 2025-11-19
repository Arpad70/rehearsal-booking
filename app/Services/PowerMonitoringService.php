<?php

namespace App\Services;

use App\Models\Device;
use App\Models\PowerMonitoring;
use Illuminate\Support\Facades\Log;

class PowerMonitoringService
{
    private ShellyGen2Service $shellyService;

    public function __construct()
    {
        $this->shellyService = new ShellyGen2Service();
    }

    /**
     * Collect power data from a Shelly device and store it
     */
    public function collectDeviceData(Device $device): bool
    {
        try {
            if (!$this->shellyService->isReachable($device)) {
                Log::warning('PowerMonitoringService: Device not reachable', [
                    'device_id' => $device->id,
                    'ip' => $device->ip,
                ]);
                return false;
            }

            // Get all switches for this device
            $switches = $this->shellyService->getAllSwitches($device);

            if (!$switches) {
                Log::warning('PowerMonitoringService: No switches found', [
                    'device_id' => $device->id,
                ]);
                return false;
            }

            // Store data for each channel
            foreach ($switches as $channel => $data) {
                $this->storeChannelData($device, $channel, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error('PowerMonitoringService error: ' . $e->getMessage(), [
                'device_id' => $device->id,
                'ip' => $device->ip,
            ]);
            return false;
        }
    }

    /**
     * Store power monitoring data for a specific channel
     */
    private function storeChannelData(Device $device, int $channel, array $data): void
    {
        try {
            // Determine status based on data
            $status = 'normal';
            $statusMessage = null;

            if (isset($data['temperature']) && isset($data['temperature_limit'])) {
                if ($data['temperature'] > $data['temperature_limit'] * 0.95) {
                    $status = 'alert';
                    $statusMessage = 'Temperature approaching limit';
                } elseif ($data['temperature'] > $data['temperature_limit'] * 0.80) {
                    $status = 'warning';
                    $statusMessage = 'Device running warm';
                }
            }

            if (isset($data['power']) && $data['power'] > 2000) {
                $status = 'warning';
                $statusMessage = 'High power consumption';
            }

            $record = PowerMonitoring::create([
                'device_id' => $device->id,
                'room_id' => $device->room_id,
                'channel' => $channel,
                'channel_name' => $device->meta['channels'][$channel]['name'] ?? "Channel {$channel}",
                'voltage' => $data['voltage'] ?? null,
                'current' => $data['current'] ?? null,
                'power' => $data['power'] ?? null,
                'power_factor' => $data['power_factor'] ?? null,
                'energy_total' => $data['energy'] ?? null,
                'energy_today' => $data['energy_today'] ?? null,
                'energy_month' => $data['energy_month'] ?? null,
                'is_on' => $data['on'] ?? false,
                'temperature' => $data['temperature'] ?? null,
                'temperature_limit' => $data['temperature_limit'] ?? null,
                'status' => $status,
                'status_message' => $statusMessage,
                'raw_data' => $data,
            ]);

            Log::info('PowerMonitoring data recorded', [
                'device_id' => $device->id,
                'channel' => $channel,
                'power' => $data['power'] ?? 0,
                'energy' => $data['energy'] ?? 0,
            ]);
        } catch (\Exception $e) {
            Log::error('PowerMonitoringService: Failed to store channel data', [
                'device_id' => $device->id,
                'channel' => $channel,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Collect data from all devices in a room
     */
    public function collectRoomData(int $roomId): int
    {
        $devices = Device::where('room_id', $roomId)
            ->where('type', 'shelly')
            ->get();

        $collected = 0;
        foreach ($devices as $device) {
            if ($this->collectDeviceData($device)) {
                $collected++;
            }
        }

        return $collected;
    }

    /**
     * Collect data from all devices in the system
     */
    public function collectAllData(): int
    {
        $devices = Device::where('type', 'shelly')->get();

        $collected = 0;
        foreach ($devices as $device) {
            if ($this->collectDeviceData($device)) {
                $collected++;
            }
        }

        Log::info('PowerMonitoringService: Collected data from devices', [
            'total_collected' => $collected,
            'total_devices' => $devices->count(),
        ]);

        return $collected;
    }

    /**
     * Get latest monitoring data for device
     */
    public function getLatestData(Device $device, int $limit = 100)
    {
        return PowerMonitoring::where('device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monitoring data for specific channel
     */
    public function getChannelData(Device $device, int $channel, int $limit = 100)
    {
        return PowerMonitoring::where('device_id', $device->id)
            ->where('channel', $channel)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get energy statistics for device
     */
    public function getEnergyStats(Device $device, int $days = 30)
    {
        $records = PowerMonitoring::where('device_id', $device->id)
            ->where('created_at', '>=', now()->subDays($days))
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        return [
            'total_energy' => $records->last()?->energy_total ?? 0,
            'today_energy' => $records->where('created_at', '>=', now()->startOfDay())->first()?->energy_today ?? 0,
            'month_energy' => $records->first()?->energy_month ?? 0,
            'average_power' => round($records->avg('power'), 2),
            'max_power' => $records->max('power'),
            'min_power' => $records->min('power'),
            'measurements_count' => $records->count(),
        ];
    }

    /**
     * Get temperature statistics
     */
    public function getTemperatureStats(Device $device, int $hours = 24)
    {
        $records = PowerMonitoring::where('device_id', $device->id)
            ->whereNotNull('temperature')
            ->where('created_at', '>=', now()->subHours($hours))
            ->get();

        if ($records->isEmpty()) {
            return null;
        }

        return [
            'current_temp' => $records->first()?->temperature,
            'average_temp' => round($records->avg('temperature'), 2),
            'max_temp' => $records->max('temperature'),
            'min_temp' => $records->min('temperature'),
            'limit_temp' => $records->first()?->temperature_limit,
        ];
    }

    /**
     * Alert if device is consuming excessive power
     */
    public function checkExcessivePowerConsumption(Device $device, float $threshold = 2000): ?PowerMonitoring
    {
        $latest = PowerMonitoring::where('device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latest && $latest->isConsumingExcessivePower($threshold)) {
            Log::warning('PowerMonitoringService: Excessive power consumption detected', [
                'device_id' => $device->id,
                'power' => $latest->power,
                'threshold' => $threshold,
            ]);

            return $latest;
        }

        return null;
    }

    /**
     * Alert if device is overheating
     */
    public function checkOverheating(Device $device): ?PowerMonitoring
    {
        $latest = PowerMonitoring::where('device_id', $device->id)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($latest && $latest->isOverheating()) {
            Log::warning('PowerMonitoringService: Device overheating detected', [
                'device_id' => $device->id,
                'temperature' => $latest->temperature,
                'limit' => $latest->temperature_limit,
            ]);

            return $latest;
        }

        return null;
    }

    /**
     * Clean up old monitoring data (keep last N days)
     */
    public function cleanupOldData(int $days = 90): int
    {
        return PowerMonitoring::where('created_at', '<', now()->subDays($days))
            ->delete();
    }
}
