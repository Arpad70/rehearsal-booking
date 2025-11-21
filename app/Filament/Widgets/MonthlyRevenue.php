<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Reservation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyRevenue extends BaseWidget
{
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();

        // Příjmy
        $paymentsSum = Payment::where('created_at', '>=', $monthStart)->sum('amount');

        $reservationsWithoutPaymentsSum = Reservation::where('created_at', '>=', $monthStart)
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('payments')
            ->sum('price');

        $monthlyRevenue = (float) $paymentsSum + (float) $reservationsWithoutPaymentsSum;

        // Noví uživatelé
        $newUsers = User::where('created_at', '>=', $monthStart)->count();

        return [
            Stat::make('Příjmy tento měsíc', number_format($monthlyRevenue, 0, ',', ' ') . ' Kč')
                ->description('Součet plateb + rezervace bez platby')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
            Stat::make('Noví uživatelé', $newUsers)
                ->description('Registrovaní noví zákazníci za aktuální měsíc')
                ->descriptionIcon('heroicon-m-user-plus')
                ->color('primary')
                ->icon('heroicon-o-user-plus'),
        ];
    }
}
