<?php

namespace App\Filament\Resources\DeviceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class HealthChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'healthChecks';

    protected static ?string $title = 'Historie kontrol';

    protected static ?string $recordTitleAttribute = 'checked_at';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        'error' => 'danger',
                        'degraded' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'online' => 'heroicon-o-check-circle',
                        'offline' => 'heroicon-o-x-circle',
                        'error' => 'heroicon-o-exclamation-circle',
                        'degraded' => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-question-mark-circle',
                    }),

                Tables\Columns\TextColumn::make('response_time_ms')
                    ->label('Odezva')
                    ->suffix(' ms')
                    ->sortable()
                    ->color(fn ($state): string => match(true) {
                        !$state => 'gray',
                        $state < 50 => 'success',
                        $state < 100 => 'info',
                        $state < 200 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Chybová zpráva')
                    ->limit(50)
                    ->tooltip(fn ($state): string => $state ?? '')
                    ->default('—'),

                Tables\Columns\TextColumn::make('checked_at')
                    ->label('Čas kontroly')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'online' => 'Online',
                        'offline' => 'Offline',
                        'error' => 'Chyba',
                        'degraded' => 'Degradován',
                    ]),
            ])
            ->defaultSort('checked_at', 'desc')
            ->poll('30s');
    }
}
