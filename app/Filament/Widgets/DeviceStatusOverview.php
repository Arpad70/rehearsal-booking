<?php

namespace App\Filament\Widgets;

use App\Models\Device;
use App\Models\DeviceHealthCheck;
use App\Services\DeviceHealthService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DeviceStatusOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    protected static ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $healthService = app(DeviceHealthService::class);
        $stats = $healthService->getAvailabilityStats();

        // Get devices by type
        $devicesByType = Device::selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type');

        // Get recent health checks (last 5 minutes)
        $recentChecks = DeviceHealthCheck::where('checked_at', '>=', now()->subMinutes(5))
            ->distinct('device_id')
            ->count();

        // Calculate average response time for online devices
        $avgResponseTime = DeviceHealthCheck::online()
            ->recent(5)
            ->avg('response_time_ms');

        return [
            Stat::make('Celkem zařízení', $stats['total'])
                ->description('Všechna registrovaná zařízení')
                ->descriptionIcon('heroicon-m-server')
                ->color('primary')
                ->icon('heroicon-o-server'),

            Stat::make('Online zařízení', $stats['online'])
                ->description($stats['offline'] . ' offline')
                ->descriptionIcon($stats['online'] >= $stats['offline'] ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($stats['online'] >= $stats['offline'] ? 'success' : 'warning')
                ->icon('heroicon-o-signal'),

            Stat::make('Dostupnost', number_format($stats['availability_percentage'], 1) . '%')
                ->description('Úspěšnost připojení')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color($this->getAvailabilityColor($stats['availability_percentage']))
                ->icon('heroicon-o-check-circle')
                ->chart($this->getAvailabilityChart()),

            Stat::make('Průměrná odezva', $avgResponseTime ? number_format($avgResponseTime, 0) . ' ms' : 'N/A')
                ->description('Online zařízení')
                ->descriptionIcon('heroicon-m-clock')
                ->color($this->getResponseTimeColor($avgResponseTime))
                ->icon('heroicon-o-clock'),
        ];
    }

    protected function getAvailabilityColor(float $percentage): string
    {
        return match(true) {
            $percentage >= 95 => 'success',
            $percentage >= 80 => 'warning',
            default => 'danger',
        };
    }

    protected function getResponseTimeColor(?float $ms): string
    {
        if (!$ms) return 'gray';
        
        return match(true) {
            $ms < 50 => 'success',
            $ms < 100 => 'info',
            $ms < 200 => 'warning',
            default => 'danger',
        };
    }

    protected function getAvailabilityChart(): array
    {
        // Get availability for last 7 checks (simplified trend)
        $checks = DeviceHealthCheck::selectRaw('
                DATE_FORMAT(checked_at, "%Y-%m-%d %H:%i") as time,
                COUNT(*) as total,
                SUM(CASE WHEN status = "online" THEN 1 ELSE 0 END) as online
            ')
            ->where('checked_at', '>=', now()->subHour())
            ->groupBy('time')
            ->orderBy('time')
            ->limit(7)
            ->get();

        return $checks->map(function($check) {
            return $check->total > 0 ? round(($check->online / $check->total) * 100) : 0;
        })->toArray();
    }
}
