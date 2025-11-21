<?php

namespace App\Services;

use App\Models\Device;
use App\Models\DeviceHealthCheck;
use App\Services\DeviceServices\QRReaderService;
use App\Services\DeviceServices\ShellyService;
use App\Services\DeviceServices\KeypadService;
use App\Services\DeviceServices\CameraService;
use App\Services\DeviceServices\MixerService;
use Illuminate\Support\Facades\Log;

class DeviceHealthService
{
    /**
     * Provést health check na zařízení
     */
    public function performHealthCheck(Device $device): array
    {
        $service = $this->getDeviceService($device);
        
        if (!$service) {
            Log::warning("Unknown device type: {$device->type}");
            return [
                'status' => 'error',
                'message' => 'Unknown device type'
            ];
        }
        
        $result = $service->healthCheck();
        
        // Uložit do databáze
        DeviceHealthCheck::create([
            'device_id' => $device->id,
            'status' => $result['status'],
            'response_time_ms' => $result['response_time_ms'] ?? null,
            'diagnostics' => $result['details'] ?? null,
            'error_message' => $result['details']['message'] ?? null,
            'checked_at' => now(),
        ]);
        
        return $result;
    }

    /**
     * Zjistit zda je zařízení online
     */
    public function isOnline(int $deviceId, int $minutes = 5): bool
    {
        return DeviceHealthCheck::where('device_id', $deviceId)
            ->online()
            ->recent($minutes)
            ->exists();
    }

    /**
     * Získat poslední health check pro zařízení
     */
    public function getLastHealthCheck(int $deviceId): ?DeviceHealthCheck
    {
        return DeviceHealthCheck::where('device_id', $deviceId)
            ->orderBy('checked_at', 'desc')
            ->first();
    }

    /**
     * Provést health check na všech zařízeních
     */
    public function checkAllDevices(): array
    {
        $devices = Device::all();
        $results = [];
        
        foreach ($devices as $device) {
            $results[$device->id] = $this->performHealthCheck($device);
        }
        
        return $results;
    }

    /**
     * Získat statistiky dostupnosti
     */
    public function getAvailabilityStats(): array
    {
        $total = Device::count();
        
        $online = Device::whereHas('healthChecks', function($query) {
            $query->online()->recent(5);
        })->count();
        
        $offline = $total - $online;
        $availability = $total > 0 ? round(($online / $total) * 100, 2) : 0;
        
        return [
            'total' => $total,
            'online' => $online,
            'offline' => $offline,
            'availability_percentage' => $availability,
        ];
    }

    /**
     * Získat správný service pro typ zařízení
     */
    private function getDeviceService(Device $device)
    {
        $port = $this->getDevicePort($device);
        $deviceId = $device->meta['name'] ?? $device->ip ?? "device-{$device->id}";
        
        return match($device->type) {
            'qr_reader' => new QRReaderService($deviceId, $port),
            'shelly' => new ShellyService($deviceId, $port),
            'keypad' => new KeypadService($deviceId, $port),
            'camera' => new CameraService($deviceId, $port),
            'mixer' => new MixerService($deviceId, $port),
            default => null
        };
    }

    /**
     * Získat port pro zařízení na základě typu a metadata
     */
    private function getDevicePort(Device $device): int
    {
        // Nejprve zkusit získat port z meta dat
        $meta = $device->meta ?? [];
        if (is_array($meta) && isset($meta['port'])) {
            return (int) $meta['port'];
        }
        
        // Pokud není v meta, zkusit parsovat z IP (např. "172.17.0.1:9401")
        if (strpos($device->ip, ':') !== false) {
            $parts = explode(':', $device->ip);
            if (count($parts) === 2 && is_numeric($parts[1])) {
                return (int) $parts[1];
            }
        }
        
        // Fallback na base port podle typu
        $basePorts = config('devices.ports', [
            'qr_reader' => 9101,
            'camera' => 9201,
            'shelly' => 9501,  // Správný port pro Shelly simulátory
            'keypad' => 9401,
            'mixer' => 9301,
        ]);
        
        return $basePorts[$device->type] ?? 9000;
    }
}
