<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class MonthlyRevenue extends BaseWidget
{
    protected static ?int $sort = 2;
    
    protected int | string | array $columnSpan = 3;

    protected function getStats(): array
    {
        $monthStart = now()->startOfMonth();

        $paymentsSum = Payment::where('created_at', '>=', $monthStart)->sum('amount');

        $reservationsWithoutPaymentsSum = Reservation::where('created_at', '>=', $monthStart)
            ->where('status', '!=', 'cancelled')
            ->whereDoesntHave('payments')
            ->sum('price');

        $monthlyRevenue = (float) $paymentsSum + (float) $reservationsWithoutPaymentsSum;

        return [
            Stat::make('Příjmy tento měsíc', number_format($monthlyRevenue, 0, ',', ' ') . ' Kč')
                ->description('Součet plateb + rezervace bez platby')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->icon('heroicon-o-currency-dollar'),
        ];
    }
}
