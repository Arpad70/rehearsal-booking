<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use App\Models\Reservation;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class EquipmentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $totalEquipment = Equipment::count();
        $availableEquipment = Equipment::where('status', 'available')->count();
        $inUseEquipment = Equipment::where('status', 'in_use')->count();
        $maintenanceEquipment = Equipment::whereIn('status', ['maintenance', 'repair'])->count();
        $criticalEquipment = Equipment::where('is_critical', true)->count();
        
        $todayReservations = Reservation::whereDate('start_at', today())->count();

        return [
            Stat::make('Celkem vybavení', $totalEquipment)
                ->description('Veškeré zařízení v systému')
                ->descriptionIcon('heroicon-m-cube')
                ->color('primary')
                ->chart([7, 12, 15, 18, 20, 22, $totalEquipment]),
            
            Stat::make('Dostupné', $availableEquipment)
                ->description(round(($availableEquipment / max($totalEquipment, 1)) * 100) . '% celkového vybavení')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([15, 18, 16, 20, 19, 22, $availableEquipment]),
            
            Stat::make('Používané', $inUseEquipment)
                ->description(round(($inUseEquipment / max($totalEquipment, 1)) * 100) . '% využití')
                ->descriptionIcon('heroicon-m-clock')
                ->color('info'),
            
            Stat::make('Údržba/Oprava', $maintenanceEquipment)
                ->description('Vybavení mimo provoz')
                ->descriptionIcon('heroicon-m-wrench')
                ->color('warning'),
            
            Stat::make('Kritické vybavení', $criticalEquipment)
                ->description('Důležité zařízení')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
            
            Stat::make('Rezervace dnes', $todayReservations)
                ->description('Aktivní rezervace')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('success'),
        ];
    }

    protected function getPollingInterval(): ?string
    {
        return '30s'; // Aktualizace každých 30 sekund
    }
}
