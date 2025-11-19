<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class NewUsersThisMonth extends BaseWidget
{
    // Umístíme tento widget těsně za MonthlyRevenue a před grafem Power Consumption
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();

        $newUsers = User::where('created_at', '>=', $monthStart)->count();

        return [
            Stat::make('Noví uživatelé (tento měsíc)', $newUsers)
                ->description('Registrovaní noví zákazníci za aktuální měsíc')
                // 'user-add' není ve standardních heroicons — použijeme 'user-plus'
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
