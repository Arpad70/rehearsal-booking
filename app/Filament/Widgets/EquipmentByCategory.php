<?php

namespace App\Filament\Widgets;

use App\Models\Equipment;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class EquipmentByCategory extends ChartWidget
{
    protected static ?string $heading = 'Vybavení podle kategorií';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $categoryData = Equipment::select('category', DB::raw('count(*) as total'))
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Počet zařízení',
                    'data' => $categoryData->pluck('total')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6',
                        '#ec4899', '#06b6d4', '#84cc16', '#f97316', '#6366f1',
                    ],
                ],
            ],
            'labels' => $categoryData->pluck('category')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getPollingInterval(): ?string
    {
        return '60s';
    }
}
