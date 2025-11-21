<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Actions\Action;

class NotificationSettings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-bell';

    protected static string $view = 'filament.pages.notification-settings';
    
    protected static ?string $navigationLabel = 'Notifikace';
    
    protected static ?string $title = 'Nastavení notifikací';
    
    protected static ?int $navigationSort = 100;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                DatabaseNotification::query()
                    ->where('notifiable_id', Auth::id() ?? 0)
                    ->where('notifiable_type', \App\Models\User::class)
                    ->orderBy('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('created_at')
                    ->label('Datum')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                    
                TextColumn::make('data')
                    ->label('Zpráva')
                    ->formatStateUsing(fn ($state) => $state['message'] ?? 'Bez popisku')
                    ->wrap(),
                    
                TextColumn::make('read_at')
                    ->label('Přečteno')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Nepřečteno')
                    ->sortable(),
            ])
            ->actions([
                Action::make('markAsRead')
                    ->label('Označit jako přečtené')
                    ->icon('heroicon-o-check')
                    ->action(function (DatabaseNotification $record) {
                        $record->markAsRead();
                    })
                    ->visible(fn (DatabaseNotification $record) => $record->read_at === null),
                    
                Action::make('view')
                    ->label('Zobrazit')
                    ->icon('heroicon-o-eye')
                    ->url(function (DatabaseNotification $record) {
                        $data = $record->data;
                        if (isset($data['equipment_id'])) {
                            return '/admin/rfid-management/' . $data['equipment_id'] . '/edit';
                        }
                        return null;
                    })
                    ->visible(fn (DatabaseNotification $record) => isset($record->data['equipment_id'])),
            ])
            ->bulkActions([
                Action::make('markAllAsRead')
                    ->label('Označit vše jako přečtené')
                    ->icon('heroicon-o-check-circle')
                    ->action(function () {
                        DatabaseNotification::query()
                            ->where('notifiable_id', Auth::id() ?? 0)
                            ->whereNull('read_at')
                            ->update(['read_at' => now()]);
                    }),
            ]);
    }
}
