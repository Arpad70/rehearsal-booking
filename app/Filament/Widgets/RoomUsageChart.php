<?php

namespace App\Filament\Widgets;

use App\Models\Room;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class RoomUsageChart extends ChartWidget
{
    protected static ?string $heading = 'Využití místností (posledních 30 dní)';
    
    protected static ?int $sort = 5;

    protected int | string | array $columnSpan = [
        'default' => 'full',
        'sm' => 2,
        'md' => 2,
        'lg' => 2,
        'xl' => 3,
        '2xl' => 3,
    ];

    protected function getData(): array
    {
        $rooms = Room::withCount([
                'reservations' => fn($query) => $query->where('start_at', '>=', now()->subDays(30))
            ])
            ->orderByDesc('reservations_count')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Počet rezervací',
                    'data' => $rooms->pluck('reservations_count')->toArray(),
                    'backgroundColor' => [
                        '#3b82f6',
                        '#10b981',
                        '#f59e0b',
                        '#ef4444',
                        '#8b5cf6',
                        '#ec4899',
                        '#06b6d4',
                        '#14b8a6',
                        '#f97316',
                        '#6366f1',
                    ],
                ],
            ],
            'labels' => $rooms->pluck('name')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
