<?php

namespace App\Filament\Widgets;

use App\Models\PowerMonitoring;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PowerMonitoringStats extends BaseWidget
{
    protected static ?int $sort = 0;

    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // Get latest data for all devices
        $latestData = PowerMonitoring::where('created_at', '>=', now()->subHours(1))
            ->latest('created_at')
            ->get()
            ->groupBy('device_id')
            ->map(fn($records) => $records->first());

        // Calculate total power consumption
        $totalPower = $latestData->sum('power');
        $avgPower = $latestData->isNotEmpty() ? $totalPower / $latestData->count() : 0;

        // Get devices with alerts
        $alertsCount = PowerMonitoring::where('created_at', '>=', now()->subHours(1))
            ->where('status', '!=', 'normal')
            ->distinct('device_id')
            ->count('device_id');

        // Get daily energy consumption
        $todayEnergy = PowerMonitoring::where('created_at', '>=', now()->startOfDay())
            ->sum('energy_today') ?? 0;

        return [
            Stat::make('Celkový příkon', number_format($totalPower, 0, ',', ' ') . ' W')
                ->description('Všechna zařízení dohromady')
                ->descriptionIcon('heroicon-m-bolt')
                ->color('success')
                ->icon('heroicon-o-bolt'),

            Stat::make('Průměrný příkon', number_format($avgPower, 0, ',', ' ') . ' W')
                ->description('Průměr na zařízení')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->icon('heroicon-o-chart-bar'),

            Stat::make('Dnešní spotřeba', number_format($todayEnergy / 1000, 2, ',', ' ') . ' kWh')
                ->description('Celková denní spotřeba')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('success')
                ->icon('heroicon-o-calculator'),

            Stat::make('Aktivní upozornění', (string) $alertsCount)
                ->description('Zařízení s problémy')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($alertsCount > 0 ? 'danger' : 'success')
                ->icon('heroicon-o-exclamation-triangle'),
        ];
    }
}
