<?php

namespace App\Filament\Actions;

use App\Models\AccessLog;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use League\Csv\Writer;

class ExportAccessLogsAction extends Action
{
    public static function make(?string $name = null): static
    {
        return parent::make($name ?? 'export')
            ->label('Exportovat')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('gray')
            ->size(ActionSize::Small)
            ->action(static function() {
                return static::export();
            });
    }

    public static function export(): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $csv = Writer::createFromString('');
        
        // Header
        $csv->insertOne([
            'Čas',
            'Uživatel',
            'Typ přístupu',
            'Typ čtečky',
            'Výsledek',
            'IP adresa',
            'Místnost',
        ]);

        // Data
        AccessLog::with('user')
            ->orderBy('created_at', 'desc')
            ->chunk(100, function($logs) use ($csv) {
                foreach ($logs as $log) {
                    $csv->insertOne([
                        $log->created_at->format('d.m.Y H:i:s'),
                        $log->user?->name ?? 'N/A',
                        match($log->access_type) {
                            'reservation' => 'Rezervace',
                            'service' => 'Servis',
                            default => $log->access_type,
                        },
                        match($log->reader_type) {
                            'room' => 'Místnost',
                            'global' => 'Globální',
                            default => $log->reader_type,
                        },
                        $log->access_granted ? 'Přístup povolen' : 'Přístup odepřen',
                        $log->ip_address,
                        $log->user?->room?->name ?? 'N/A',
                    ]);
                }
            });

        return Response::streamDownload(
            fn() => echo $csv->getContent(),
            'access-logs-' . now()->format('Y-m-d-His') . '.csv',
            ['Content-Type' => 'text/csv']
        );
    }
}
