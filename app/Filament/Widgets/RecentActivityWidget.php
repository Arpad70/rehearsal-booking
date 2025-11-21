<?php

namespace App\Filament\Widgets;

use App\Models\AccessLog;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentActivityWidget extends BaseWidget
{
    protected static ?string $heading = 'Poslední aktivity';
    
    protected static ?int $sort = 4;
    
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                AccessLog::query()
                    ->with(['room', 'user'])
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Čas')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uživatel')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('room.name')
                    ->label('Místnost')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('action')
                    ->label('Akce')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('result')
                    ->label('Výsledek')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'success' => 'success',
                        'denied' => 'danger',
                        'error' => 'warning',
                        default => 'gray',
                    }),
                    
                Tables\Columns\IconColumn::make('access_granted')
                    ->label('Přístup')
                    ->boolean()
                    ->sortable(),
            ])
            ->poll('30s');
    }
}
