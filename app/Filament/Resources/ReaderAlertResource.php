<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReaderAlertResource\Pages;
use App\Models\ReaderAlert;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;

class ReaderAlertResource extends Resource
{
    protected static ?string $model = ReaderAlert::class;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Upozornění čteček';
    protected static ?string $modelLabel = 'upozornění čtečky';
    protected static ?string $pluralModelLabel = 'upozornění čteček';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('⚠️ Podrobnosti upozornění')
                    ->description('Informace o čtečce a typu upozornění')
                    ->schema([
                        TextInput::make('reader_type')
                            ->label('Typ čtečky')
                            ->disabled()
                            ->columnSpan(2),

                        TextInput::make('reader_id')
                            ->label('ID čtečky')
                            ->numeric()
                            ->disabled(),

                        TextInput::make('alert_type')
                            ->label('Typ upozornění')
                            ->disabled(),

                        TextInput::make('severity')
                            ->label('Závažnost')
                            ->disabled(),

                        Textarea::make('message')
                            ->label('Zpráva')
                            ->disabled()
                            ->columnSpanFull()
                            ->rows(3),
                    ]),

                Section::make('🔧 Řešení')
                    ->description('Informace o řešení problému')
                    ->schema([
                        Toggle::make('resolved')
                            ->label('Vyřešeno')
                            ->helperText('Označit upozornění jako vyřešené'),

                        Textarea::make('resolution_notes')
                            ->label('Poznámky k řešení')
                            ->placeholder('Popis provedeného řešení...')
                            ->columnSpanFull()
                            ->rows(3),

                        TextInput::make('resolved_at')
                            ->label('Čas vyřešení')
                            ->disabled(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('alert_type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'connection_failed' => 'danger',
                        'high_failure_rate' => 'warning',
                        'offline' => 'danger',
                        'configuration_error' => 'warning',
                        default => 'info',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'connection_failed' => '🔌 Selhání připojení',
                        'high_failure_rate' => '📈 Vysoká chybovost',
                        'offline' => '❌ Čtečka offline',
                        'configuration_error' => '⚙️ Chyba konfigurace',
                        default => '❓ Neznámé',
                    }),

                TextColumn::make('reader_type')
                    ->label('Čtečka')
                    ->formatStateUsing(function (string $state, ReaderAlert $record) {
                        $readerName = 'N/A';
                        if ($state === 'room_reader' && $record->roomReader) {
                            $readerName = $record->roomReader->reader_name;
                        } elseif ($state === 'global_reader' && $record->globalReader) {
                            $readerName = $record->globalReader->reader_name;
                        }
                        return "{$readerName}";
                    })
                    ->searchable(),

                BadgeColumn::make('severity')
                    ->label('Závažnost')
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'high' => 'warning',
                        'medium' => 'info',
                        'low' => 'success',
                        default => 'gray',
                    }),

                IconColumn::make('resolved')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('message')
                    ->label('Zpráva')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                TextColumn::make('resolved_at')
                    ->label('Vyřešeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('alert_type')
                    ->label('Typ upozornění')
                    ->options([
                        'connection_failed' => '🔌 Selhání připojení',
                        'high_failure_rate' => '📈 Vysoká chybovost',
                        'offline' => '❌ Offline',
                        'configuration_error' => '⚙️ Chyba konfigurace',
                    ]),

                SelectFilter::make('severity')
                    ->label('Závažnost')
                    ->options([
                        'critical' => 'Kritická',
                        'high' => 'Vysoká',
                        'medium' => 'Střední',
                        'low' => 'Nízká',
                    ]),

                SelectFilter::make('resolved')
                    ->label('Stav')
                    ->options([
                        true => '✅ Vyřešeno',
                        false => '⏳ Čeká na řešení',
                    ]),

                TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReaderAlerts::route('/'),
            'create' => Pages\CreateReaderAlert::route('/create'),
            'edit' => Pages\EditReaderAlert::route('/{record}/edit'),
        ];
    }
}
