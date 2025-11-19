<?php

namespace App\Filament\Widgets;

use App\Models\PowerMonitoring;
use App\Models\Device;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PowerConsumptionChart extends ChartWidget
{
    protected static ?string $heading = 'Current Power Consumption';
    
    protected static ?int $sort = 1;

    protected int | string | array $columnSpan = 'full';

    public function getDescription(): ?string
    {
        return 'Real-time power consumption across all devices';
    }

    protected function getData(): array
    {
        $last24Hours = PowerMonitoring::where('created_at', '>=', now()->subHours(24))
            ->orderBy('created_at')
            ->get()
            ->groupBy('device_id');

        $datasets = [];
        $labels = [];
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
        ];

        $colorIndex = 0;

        foreach ($last24Hours as $deviceId => $records) {
            $device = Device::find($deviceId);
            if (!$device) continue;

            $data = $records->map(fn($record) => [
                'x' => $record->created_at->format('H:i'),
                'y' => $record->power ?? 0,
            ])->values();

            $datasets[] = [
                'label' => $device->name,
                'data' => $data->toArray(),
                'borderColor' => $colors[$colorIndex % count($colors)],
                'backgroundColor' => str_replace(')', ', 0.1)', str_replace('rgb', 'rgba', $colors[$colorIndex % count($colors)])),
                'tension' => 0.4,
            ];

            $colorIndex++;
        }

        return [
            'datasets' => $datasets,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'callback' => 'function(value) { return value + " W"; }',
                    ],
                ],
            ],
        ];
    }
}
