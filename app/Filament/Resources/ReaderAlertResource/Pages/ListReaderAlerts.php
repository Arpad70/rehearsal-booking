<?php

namespace App\Filament\Resources\ReaderAlertResource\Pages;

use App\Filament\Resources\ReaderAlertResource;
use App\Models\ReaderAlert;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;

class ListReaderAlerts extends ListRecords
{
    protected static string $resource = ReaderAlertResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('resolve_all')
                ->label('✅ Vyřeš všechny')
                ->icon('heroicon-o-check-circle')
                ->size(ActionSize::Medium)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Vyřešit všechna upozornění')
                ->modalDescription('Tato akce označí všechna aktivní upozornění jako vyřešená')
                ->action(function () {
                    $count = ReaderAlert::where('resolved', false)->update([
                        'resolved' => true,
                        'resolved_at' => now(),
                    ]);
                    $this->notify('success', "Vyřešeno {$count} upozornění");
                }),

            Actions\CreateAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return '⚠️ Upozornění čteček';
    }
}
