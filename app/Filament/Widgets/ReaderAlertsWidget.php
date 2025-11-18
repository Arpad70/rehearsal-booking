<?php

namespace App\Filament\Widgets;

use App\Models\ReaderAlert;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ReaderAlertsWidget extends BaseWidget
{
    protected static ?string $heading = 'Aktivní upozornění čteček';
    protected static ?int $sort = 1;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ReaderAlert::unresolved()
                    ->orderBy('severity', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\BadgeColumn::make('severity')
                    ->label('Závažnost')
                    ->colors([
                        'danger' => 'critical',
                        'warning' => 'warning',
                        'info' => 'info',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'critical' => '🔴 Kritické',
                        'warning' => '🟡 Varování',
                        'info' => '🔵 Info',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('alert_type')
                    ->label('Typ')
                    ->formatStateUsing(fn($state) => match($state) {
                        'offline' => 'Offline',
                        'high_failure_rate' => 'Vysoká chybovost',
                        'no_activity' => 'Bez aktivity',
                        'suspicious_access' => 'Podezřelý přístup',
                        'configuration_error' => 'Chyba konfigurace',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('alertable')
                    ->label('Zařízení')
                    ->getStateUsing(fn(ReaderAlert $record) => 
                        $record->alertable?->reader_name ?? 'N/A'
                    ),

                Tables\Columns\TextColumn::make('message')
                    ->label('Zpráva')
                    ->limit(50),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Čas')
                    ->dateTime('H:i')
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('acknowledge')
                    ->label('Potvrdit')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(ReaderAlert $record) => !$record->acknowledged)
                    ->action(fn(ReaderAlert $record) => $record->acknowledge()),

                Tables\Actions\Action::make('resolve')
                    ->label('Vyřešit')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->action(fn(ReaderAlert $record) => $record->resolve()),
            ])
            ->paginated(false);
    }
}
