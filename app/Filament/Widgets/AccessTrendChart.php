<?php

namespace App\Filament\Widgets;

use App\Models\AccessLog;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class AccessTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Trend přístupů (posledních 7 dní)';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $startDate = Carbon::now()->subDays(6)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $data = AccessLog::selectRaw('DATE(created_at) as date')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN validation_result = 'success' THEN 1 ELSE 0 END) as successful")
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date')
            ->get();

        // Fill missing days with zeros
        $labels = [];
        $successful = [];
        $failed = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('d.m.');
            $labels[] = $date;

            $record = $data->firstWhere('date', Carbon::now()->subDays($i)->toDateString());
            $successful[] = $record?->successful ?? 0;
            $failed[] = ($record?->total ?? 0) - ($record?->successful ?? 0);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Úspěšné',
                    'data' => $successful,
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Neúspěšné',
                    'data' => $failed,
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
