<?php

namespace App\Filament\Widgets;

use App\Models\AccessLog;
use App\Models\Reservation;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AccessStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $now = Carbon::now();
        $today = $now->copy()->startOfDay();
        $week = $now->copy()->startOfWeek();
        $month = $now->copy()->startOfMonth();

        // Today's statistics
        $todayAccess = AccessLog::where('created_at', '>=', $today)
            ->where('access_granted', true)
            ->count();

        $todayFailed = AccessLog::where('created_at', '>=', $today)
            ->where('access_granted', false)
            ->count();

        // This week's reservations
        $weekReservations = Reservation::whereBetween('created_at', [$week, $now])
            ->count();

        // This month's access logs
        $monthAccess = AccessLog::where('created_at', '>=', $month)
            ->where('access_granted', true)
            ->count();

        return [
            Stat::make('Přístupy dnes', $todayAccess)
                ->description("{$todayFailed} neúspěšných")
                ->descriptionIcon('heroicon-m-arrow-trending-down', IconPosition::Before)
                ->color($todayFailed > 0 ? 'warning' : 'success'),

            Stat::make('Rezervace tento týden', $weekReservations)
                ->description('Nové rezervace')
                ->descriptionIcon('heroicon-m-calendar', IconPosition::Before)
                ->color('info'),

            Stat::make('Přístupy tento měsíc', $monthAccess)
                ->description('Úspěšné ověření')
                ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before)
                ->color('success'),

            Stat::make('Úspěšnost', $this->getSuccessRate($now) . '%')
                ->description('Poslední 30 dní')
                ->descriptionIcon('heroicon-m-chart-bar', IconPosition::Before)
                ->color('success'),
        ];
    }
    private function getSuccessRate(?Carbon $now = null): int
    {
        $now = $now ?? Carbon::now();
        $thirtyDaysAgo = $now->copy()->subDays(30)->startOfDay();

        $total = AccessLog::where('created_at', '>=', $thirtyDaysAgo)->count();
        if ($total === 0) {
            return 100;
        }

        $successful = AccessLog::where('created_at', '>=', $thirtyDaysAgo)
            ->where('access_granted', true)
            ->count();

        return (int) round(($successful / $total) * 100);
    }
}
