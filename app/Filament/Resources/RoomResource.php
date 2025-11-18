<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Jobs\ToggleShellyJob;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255)
                    ->default(null),
                Forms\Components\TextInput::make('capacity')
                    ->required()
                    ->numeric()
                    ->default(1),
                Forms\Components\TextInput::make('shelly_ip')
                    ->maxLength(255)
                    ->default(null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('location')
                    ->searchable(),
                Tables\Columns\TextColumn::make('capacity')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('shelly_ip')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleShelly')
                    ->label('Přepnout Shelly')
                    ->icon('heroicon-o-power')
                    ->action(function (Room $record): void {
                        if (! $record->shelly_ip) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Zařízení Shelly není nakonfigurováno pro tuto místnost')
                                ->send();
                            return;
                        }

                        // dispatch background job to toggle device (best-effort)
                        ToggleShellyJob::dispatch($record);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Příkaz pro zařízení {$record->name} zařazen do fronty")
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Přepnout stav Shelly zařízení?')
                    ->modalDescription('Opravdu chcete přepnout stav Shelly zařízení v této místnosti?')
                    ->modalSubmitActionLabel('Ano, přepnout')
                    ->modalCancelActionLabel('Ne, zrušit')
                    ->visible(fn (Room $record): bool => (bool) $record->shelly_ip),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
