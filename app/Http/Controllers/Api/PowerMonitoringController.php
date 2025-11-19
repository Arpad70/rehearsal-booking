<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\PowerMonitoring;
use App\Services\PowerMonitoringService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PowerMonitoringController extends Controller
{
    private PowerMonitoringService $service;

    public function __construct(PowerMonitoringService $service)
    {
        $this->service = $service;
    }

    /**
     * Collect power monitoring data for all devices
     * POST /api/v1/power-monitoring/collect
     */
    public function collectAll(): JsonResponse
    {
        $collected = $this->service->collectAllData();

        return response()->json([
            'success' => true,
            'message' => "Power data collected from {$collected} devices",
            'devices_collected' => $collected,
        ]);
    }

    /**
     * Collect power monitoring data for specific device
     * POST /api/v1/power-monitoring/collect/{deviceId}
     */
    public function collectDevice(int $deviceId): JsonResponse
    {
        $device = Device::findOrFail($deviceId);

        $collected = $this->service->collectDeviceData($device);

        return response()->json([
            'success' => $collected,
            'message' => $collected ? 'Power data collected successfully' : 'Failed to collect power data',
            'device_id' => $deviceId,
        ]);
    }

    /**
     * Get latest power monitoring data for device
     * GET /api/v1/power-monitoring/{deviceId}
     */
    public function getDeviceData(int $deviceId, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $limit = $request->get('limit', 100);

        $data = $this->service->getLatestData($device, $limit);

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'data_points' => $data->count(),
            'data' => $data->map(fn($record) => [
                'id' => $record->id,
                'channel' => $record->channel,
                'channel_name' => $record->channel_name,
                'power_w' => $record->power,
                'power_formatted' => $record->getFormattedPower(),
                'voltage_v' => $record->voltage,
                'current_a' => $record->current,
                'power_factor' => $record->power_factor,
                'energy_total' => $record->energy_total,
                'energy_total_formatted' => $record->getFormattedEnergy(),
                'energy_today' => $record->energy_today,
                'energy_month' => $record->energy_month,
                'is_on' => $record->is_on,
                'temperature_c' => $record->temperature,
                'status' => $record->status,
                'status_message' => $record->status_message,
                'consumption_status' => $record->getConsumptionStatus(),
                'created_at' => $record->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Get power monitoring data for specific channel
     * GET /api/v1/power-monitoring/{deviceId}/channel/{channel}
     */
    public function getChannelData(int $deviceId, int $channel, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $limit = $request->get('limit', 100);

        $data = $this->service->getChannelData($device, $channel, $limit);

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'channel' => $channel,
            'data_points' => $data->count(),
            'data' => $data->map(fn($record) => [
                'id' => $record->id,
                'power_w' => $record->power,
                'voltage_v' => $record->voltage,
                'current_a' => $record->current,
                'energy_total' => $record->energy_total,
                'is_on' => $record->is_on,
                'temperature_c' => $record->temperature,
                'created_at' => $record->created_at->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Get energy statistics for device
     * GET /api/v1/power-monitoring/{deviceId}/stats/energy
     */
    public function getEnergyStats(int $deviceId, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $days = $request->get('days', 30);

        $stats = $this->service->getEnergyStats($device, $days);

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'period_days' => $days,
            'stats' => $stats ?? [
                'message' => 'No data available for this period',
            ],
        ]);
    }

    /**
     * Get temperature statistics for device
     * GET /api/v1/power-monitoring/{deviceId}/stats/temperature
     */
    public function getTemperatureStats(int $deviceId, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $hours = $request->get('hours', 24);

        $stats = $this->service->getTemperatureStats($device, $hours);

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'period_hours' => $hours,
            'stats' => $stats ?? [
                'message' => 'No temperature data available',
            ],
        ]);
    }

    /**
     * Get daily energy summary for device
     * GET /api/v1/power-monitoring/{deviceId}/daily
     */
    public function getDailyEnergy(int $deviceId, Request $request): JsonResponse
    {
        $device = Device::findOrFail($deviceId);
        $days = $request->get('days', 30);

        $data = PowerMonitoring::getDailyEnergy($device->id, $days);

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'days' => $days,
            'data' => $data->map(fn($record) => [
                'date' => $record->date,
                'energy_wh' => $record->energy_wh,
                'avg_power_w' => round($record->avg_power_w, 2),
            ]),
        ]);
    }

    /**
     * Check device alerts
     * GET /api/v1/power-monitoring/{deviceId}/alerts
     */
    public function getAlerts(int $deviceId): JsonResponse
    {
        $device = Device::findOrFail($deviceId);

        $alerts = [];

        // Check power consumption
        $powerAlert = $this->service->checkExcessivePowerConsumption($device);
        if ($powerAlert) {
            $alerts[] = [
                'type' => 'excessive_power',
                'power_w' => $powerAlert->power,
                'threshold_w' => 2000,
                'severity' => 'warning',
            ];
        }

        // Check temperature
        $tempAlert = $this->service->checkOverheating($device);
        if ($tempAlert) {
            $alerts[] = [
                'type' => 'overheating',
                'temperature_c' => $tempAlert->temperature,
                'limit_c' => $tempAlert->temperature_limit,
                'severity' => 'critical',
            ];
        }

        // Get recent warning/alert statuses
        $statusAlerts = PowerMonitoring::where('device_id', $deviceId)
            ->where('status', '!=', 'normal')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        foreach ($statusAlerts as $record) {
            $alerts[] = [
                'type' => 'status_alert',
                'channel' => $record->channel,
                'status' => $record->status,
                'message' => $record->status_message,
                'created_at' => $record->created_at->toIso8601String(),
            ];
        }

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'alerts_count' => count($alerts),
            'alerts' => $alerts,
        ]);
    }

    /**
     * Get latest single data point for device
     * GET /api/v1/power-monitoring/{deviceId}/latest
     */
    public function getLatest(int $deviceId): JsonResponse
    {
        $device = Device::findOrFail($deviceId);

        $latest = PowerMonitoring::where('device_id', $deviceId)
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$latest) {
            return response()->json([
                'success' => false,
                'message' => 'No monitoring data available yet',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'device_id' => $deviceId,
            'data' => [
                'id' => $latest->id,
                'channel' => $latest->channel,
                'power_w' => $latest->power,
                'power_formatted' => $latest->getFormattedPower(),
                'voltage_v' => $latest->voltage,
                'current_a' => $latest->current,
                'energy_total' => $latest->energy_total,
                'energy_total_formatted' => $latest->getFormattedEnergy(),
                'is_on' => $latest->is_on,
                'temperature_c' => $latest->temperature,
                'status' => $latest->status,
                'consumption_status' => $latest->getConsumptionStatus(),
                'created_at' => $latest->created_at->toIso8601String(),
            ],
        ]);
    }
}
