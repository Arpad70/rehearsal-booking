<?php

namespace App\Filament\Widgets;

use App\Models\PowerMonitoring;
use App\Models\Device;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class PowerConsumptionChart extends ChartWidget
{
    protected static ?string $heading = 'Current Power Consumption';
    
    // Posuneme graf níže, aby se nad ním zobrazily obchodní widgety
    protected static ?int $sort = 5;

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

        $deviceIds = $last24Hours->keys()->toArray();

        // Preload devices to avoid N+1 queries
        $devices = Device::whereIn('id', $deviceIds)->get()->keyBy('id');

        $datasets = [];
        $labelsCollection = collect();
        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#FF6384', '#C9CBCF', '#4BC0C0', '#FF6384',
        ];

        $colorIndex = 0;

        foreach ($last24Hours as $deviceId => $records) {
            $device = $devices->get($deviceId);
            if (!$device) {
                continue;
            }

            $records = $records->values();

            // Collect labels (time points) for a shared labels array
            $labelsCollection = $labelsCollection->merge($records->map(fn($r) => $r->created_at->format('H:i')));

            $data = $records->map(fn($record) => [
                'x' => $record->created_at->format('H:i'),
                'y' => $record->power ?? 0,
            ])->values();

            $borderColor = $colors[$colorIndex % count($colors)];
            $backgroundColor = $this->hexToRgba($borderColor, 0.08);

            $datasets[] = [
                'label' => $device->name ?? "Device {$deviceId}",
                'data' => $data->toArray(),
                'borderColor' => $borderColor,
                'backgroundColor' => $backgroundColor,
                'tension' => 0.4,
            ];

            $colorIndex++;
        }

        $labels = $labelsCollection->unique()->values()->toArray();

        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }

    /**
     * Convert hex color to rgba string with given alpha.
     */
    private function hexToRgba(string $hex, float $alpha = 0.1): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "rgba({$r}, {$g}, {$b}, {$alpha})";
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
