<?php

namespace App\Filament\Resources\RoomResource\Pages;

use App\Filament\Resources\RoomResource;
use App\Models\Room;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\ListRecords\Tab;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;

class ListRooms extends ListRecords
{
    protected static string $resource = RoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\EditAction::make(),
            Tables\Actions\Action::make('toggleShelly')
                ->label('Toggle Shelly')
                ->icon('heroicon-o-switch-horizontal')
                ->action(function (Room $record) {
                    Notification::make()
                        ->title("Odesláno příkazu pro zařízení {$record->name}")
                        ->success()
                        ->send();
                })
                ->requiresConfirmation()
                ->visible(fn (Room $record) => (bool) $record->shelly_ip),
            Tables\Actions\DeleteAction::make(),
        ];
    }

    protected function getTableBulkActions(): array
    {
        return [
            Tables\Actions\BulkAction::make('deleteSelected')
                ->label('Smazat vybrané')
                ->action(fn (array $records) => $this->bulkDelete($records))
                ->requiresConfirmation()
                ->deselectRecordsAfterCompletion(),
        ];
    }

    protected function bulkDelete(array $records): void
    {
        foreach ($records as $record) {
            $record->delete();
        }

        Notification::make()
            ->title('Vybrané místnosti byly smazány')
            ->success()
            ->send();

        $this->refresh();
    }

    protected function getTableFilters(): array
    {
        return [
            SelectFilter::make('location')
                ->label('Lokace')
                ->options(fn () => Room::query()->pluck('location', 'location')->unique()->filter()->toArray()),

            TernaryFilter::make('has_shelly')
                ->label('Má Shelly')
                ->trueLabel('Ano')
                ->falseLabel('Ne')
                ->queries(
                    true: fn (Builder $query) => $query->whereNotNull('shelly_ip')->where('shelly_ip', '!=', ''),
                    false: fn (Builder $query) => $query->whereNull('shelly_ip')->orWhere('shelly_ip', '')
                ),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();

        // případné filtrování podle company_id atd.

        return $query;
    }
}
