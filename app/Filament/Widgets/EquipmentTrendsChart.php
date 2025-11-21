<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;

class EquipmentTrendsChart extends ChartWidget
{
    protected static ?string $heading = 'Využití vybavení (30 dní)';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $days = collect(range(29, 0))->map(function ($daysAgo) {
            return now()->subDays($daysAgo)->format('Y-m-d');
        });

        // Simulace trendů - v produkci by se čerpalo z access_logs
        $available = [];
        $inUse = [];
        
        foreach ($days as $day) {
            $total = Equipment::count();
            // Simulace dat - nahraďte skutečným dotazem na access_logs
            $used = rand(10, min(50, $total));
            $available[] = $total - $used;
            $inUse[] = $used;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Dostupné',
                    'data' => $available,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Používané',
                    'data' => $inUse,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $days->map(fn($d) => date('d.m.', strtotime($d)))->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
    
    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
