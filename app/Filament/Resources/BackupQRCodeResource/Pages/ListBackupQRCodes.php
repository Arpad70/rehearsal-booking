<?php

namespace App\Filament\Resources\BackupQRCodeResource\Pages;

use App\Filament\Resources\BackupQRCodeResource;
use App\Models\BackupQRCode;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;
use Illuminate\Support\Facades\Storage;

class ListBackupQRCodes extends ListRecords
{
    protected static string $resource = BackupQRCodeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_backups')
                ->label('🔄 Vygeneruj zálohy')
                ->icon('heroicon-o-arrow-path')
                ->size(ActionSize::Medium)
                ->color('info')
                ->requiresConfirmation()
                ->modalHeading('Vygenerovat záložní QR kódy')
                ->modalDescription('Vytvoří nové záložní QR kódy pro všechny rezervace bez záloh')
                ->action(function () {
                    $count = BackupQRCode::generateMissingBackups();
                    $this->notify('success', "Vytvořeno {$count} nových záložních QR kódů");
                }),

            Actions\Action::make('export_all')
                ->label('📥 Export všech')
                ->icon('heroicon-o-arrow-down-tray')
                ->size(ActionSize::Medium)
                ->color('success')
                ->action(function () {
                    try {
                        $file = BackupQRCode::exportAsZip();
                        $this->notify('success', 'Soubor je připraven k stažení');
                        return redirect()->download(storage_path("app/{$file}"));
                    } catch (\Exception $e) {
                        $this->notify('danger', 'Chyba: ' . $e->getMessage());
                    }
                }),

            Actions\CreateAction::make(),
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [10, 25, 50, 100];
    }
}
