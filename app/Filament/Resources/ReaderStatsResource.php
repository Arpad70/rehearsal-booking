<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReaderStatsResource\Pages;
use App\Models\RoomReader;
use App\Models\GlobalReader;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ReaderStatsResource extends Resource
{
    protected static ?string $model = RoomReader::class;

    protected static ?string $slug = 'reader-statistics';

    protected static ?string $navigationIcon = 'heroicon-o-signal';

    protected static ?string $navigationGroup = 'Reporty';

    protected static ?string $navigationLabel = 'Statistiky čteček';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('room.name')
                    ->label('Místnost')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('reader_name')
                    ->label('Jméno čtečky')
                    ->searchable(),

                Tables\Columns\TextColumn::make('reader_ip')
                    ->label('IP adresa')
                    ->copyable()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('door_lock_type')
                    ->label('Typ zámku')
                    ->colors([
                        'blue' => 'relay',
                        'purple' => 'api',
                        'green' => 'webhook',
                    ])
                    ->formatStateUsing(fn($state) => ucfirst($state)),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Aktivní')
                    ->boolean(),

                Tables\Columns\TextColumn::make('accessLogs')
                    ->label('Pokusy (30d)')
                    ->getStateUsing(function(RoomReader $record) {
                        return $record->getAccessAttemptsLast30Days();
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('successRate')
                    ->label('Úspěšnost')
                    ->getStateUsing(function(RoomReader $record) {
                        return round($record->getSuccessRate(), 1) . '%';
                    })
                    ->color(function(RoomReader $record) {
                        $rate = $record->getSuccessRate();
                        return $rate >= 95 ? 'success' : ($rate >= 80 ? 'warning' : 'danger');
                    })
                    ->sortable(false),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Vytvořena')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50])
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReaderStats::route('/'),
        ];
    }
}
