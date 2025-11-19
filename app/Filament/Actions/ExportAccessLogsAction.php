<?php

namespace App\Filament\Actions;

use App\Models\AccessLog;
use Filament\Actions\Action;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Response;

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
        $filename = 'access-logs-' . now()->format('Y-m-d-His') . '.csv';

        $callback = function () {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            /** @var \Illuminate\Support\Collection $logs */
            /** @var \App\Models\AccessLog $log */

            // Header
            fputcsv($handle, [
                'Čas',
                'Uživatel',
                'Typ přístupu',
                'Typ čtečky',
                'Výsledek',
                'IP adresa',
                'Místnost',
            ]);

            AccessLog::with(['user', 'user.room'])
                ->orderBy('created_at', 'desc')
                ->chunk(100, function ($logs) use ($handle) {
                    foreach ($logs as $log) {
                        $accessType = match($log->access_type) {
                            'reservation' => 'Rezervace',
                            'service' => 'Servis',
                            default => $log->access_type,
                        };

                        $readerType = match($log->reader_type) {
                            'room' => 'Místnost',
                            'global' => 'Globální',
                            default => $log->reader_type,
                        };

                        fputcsv($handle, [
                            $log->created_at?->format('d.m.Y H:i:s') ?? '',
                            $log->user?->name ?? 'N/A',
                            $accessType,
                            $readerType,
                            $log->access_granted ? 'Přístup povolen' : 'Přístup odepřen',
                            $log->ip_address,
                            $log->user?->room?->name ?? 'N/A',
                        ]);
                    }
                });

            fclose($handle);
        };

        return Response::streamDownload(
            $callback,
            $filename,
            ['Content-Type' => 'text/csv']
        );
    }
}
