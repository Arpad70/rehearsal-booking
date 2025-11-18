<?php

namespace App\Filament\Resources\AccessReportResource\Widgets;

use App\Models\AccessLog;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Carbon;

class AccessReportStats extends BaseWidget
{
    protected function getStats(): array
    {
        $thirtyDaysAgo = Carbon::now()->subDays(30)->startOfDay();
        
        // All time stats
        $totalAccess = AccessLog::count();
        $successfulAccess = AccessLog::where('access_granted', true)->count();
        
        // Last 30 days
        $thirtyDaysAccess = AccessLog::where('created_at', '>=', $thirtyDaysAgo)->count();
        $thirtyDaysSuccessful = AccessLog::where('created_at', '>=', $thirtyDaysAgo)
            ->where('access_granted', true)
            ->count();

        $successRate = $thirtyDaysAccess > 0 
            ? round(($thirtyDaysSuccessful / $thirtyDaysAccess) * 100, 1)
            : 100;

        // Failure breakdown last 30 days
        $thirtyDaysFailed = $thirtyDaysAccess - $thirtyDaysSuccessful;

        return [
            Stat::make('Celkem pokusů', $totalAccess)
                ->description('Od začátku')
                ->descriptionIcon('heroicon-m-check-circle', IconPosition::Before),

            Stat::make('Úspěšnost', "{$successRate}%")
                ->description("Z {$thirtyDaysAccess} pokusů (30 dní)")
                ->descriptionIcon('heroicon-m-chart-pie', IconPosition::Before)
                ->color($successRate >= 95 ? 'success' : ($successRate >= 80 ? 'warning' : 'danger')),

            Stat::make('Neúspěšné pokusy (30d)', $thirtyDaysFailed)
                ->description("Z {$thirtyDaysAccess} celkem")
                ->descriptionIcon('heroicon-m-exclamation-circle', IconPosition::Before)
                ->color($thirtyDaysFailed > 0 ? 'warning' : 'success'),

            Stat::make('Úspěšné pokusy (30d)', $thirtyDaysSuccessful)
                ->description('Ověřeno bez problémů')
                ->descriptionIcon('heroicon-m-shield-check', IconPosition::Before)
                ->color('success'),
        ];
    }
}
