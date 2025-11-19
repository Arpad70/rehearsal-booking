<?php

namespace App\Filament\Resources;

use App\Models\PowerMonitoring;
use App\Filament\Resources\PowerMonitoringResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use App\Models\Device;
use Illuminate\Database\Eloquent\Builder;

class PowerMonitoringResource extends Resource
{
    protected static ?string $model = PowerMonitoring::class;

    protected static ?string $navigationIcon = 'heroicon-o-bolt';
    
    protected static ?string $navigationLabel = 'Power Monitoring';
    
    protected static ?string $title = 'Power Monitoring';

    protected static ?int $navigationSort = 8;

    protected static ?string $navigationGroup = 'System';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Device & Channel')
                    ->schema([
                        Forms\Components\Select::make('device_id')
                            ->options(fn () => Device::orderBy('id')
                                ->get()
                                ->mapWithKeys(fn ($d) => [ $d->id => $d->meta['name'] ?? $d->ip ?? ('Device ' . $d->id) ])
                                ->toArray()
                            )
                            ->required()
                            ->disabled(),
                        Forms\Components\TextInput::make('channel')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'name')
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Power Metrics')
                    ->schema([
                        Forms\Components\TextInput::make('voltage')
                            ->label('Voltage (V)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('current')
                            ->label('Current (A)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('power')
                            ->label('Power (W)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('power_factor')
                            ->label('Power Factor')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Energy Consumption')
                    ->schema([
                        Forms\Components\TextInput::make('energy_total')
                            ->label('Total Energy (Wh)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('energy_today')
                            ->label('Today Energy (Wh)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('energy_month')
                            ->label('Month Energy (Wh)')
                            ->numeric()
                            ->disabled(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Thermal & State')
                    ->schema([
                        Forms\Components\TextInput::make('temperature')
                            ->label('Temperature (°C)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('temperature_limit')
                            ->label('Temperature Limit (°C)')
                            ->numeric()
                            ->disabled(),
                        Forms\Components\Toggle::make('is_on')
                            ->label('Relay On')
                            ->disabled(),
                        Forms\Components\TextInput::make('last_switched_at')
                            ->label('Last Switched At')
                            ->disabled(),
                    ])
                    ->columns(4),

                Forms\Components\Section::make('Status & Alerts')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'normal' => 'Normal',
                                'warning' => 'Warning',
                                'alert' => 'Alert',
                            ])
                            ->disabled(),
                        Forms\Components\TextInput::make('status_message')
                            ->label('Status Message')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Raw Data')
                    ->schema([
                        Forms\Components\Textarea::make('raw_data')
                            ->label('Raw JSON Data')
                            ->disabled()
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('device.name')
                    ->label('Device')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('channel')
                    ->numeric()
                    ->sortable(),
                
                TextColumn::make('room.name')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),
                
                TextColumn::make('power')
                    ->label('Power')
                    ->formatStateUsing(fn($state) => $state ? number_format($state, 0) . ' W' : '-')
                    ->sortable()
                    ->alignment('right'),
                
                TextColumn::make('energy_total')
                    ->label('Total Energy')
                    ->formatStateUsing(fn($state) => $state ? number_format($state / 1000, 2) . ' kWh' : '-')
                    ->sortable()
                    ->alignment('right'),
                
                TextColumn::make('temperature')
                    ->label('Temp')
                    ->formatStateUsing(fn($state) => $state ? $state . '°C' : '-')
                    ->sortable()
                    ->alignment('center'),
                
                IconColumn::make('is_on')
                    ->label('On')
                    ->boolean()
                    ->sortable()
                    ->alignment('center'),
                
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->colors([
                        'success' => 'normal',
                        'warning' => 'warning',
                        'danger' => 'alert',
                    ])
                    ->sortable()
                    ->alignment('center'),
                
                TextColumn::make('created_at')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('device_id')
                    ->options(fn () => Device::orderBy('id')
                        ->get()
                        ->mapWithKeys(fn ($d) => [ $d->id => $d->meta['name'] ?? $d->ip ?? ('Device ' . $d->id) ])
                        ->toArray()
                    ),
                
                SelectFilter::make('room_id')
                    ->relationship('room', 'name'),
                
                SelectFilter::make('status')
                    ->options([
                        'normal' => 'Normal',
                        'warning' => 'Warning',
                        'alert' => 'Alert',
                    ]),
                
                SelectFilter::make('is_on')
                    ->options([
                        true => 'On',
                        false => 'Off',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // No dangerous bulk actions for monitoring data
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([50, 100, 200])
            ->striped();
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
            'index' => Pages\ListPowerMonitorings::route('/'),
            'view' => Pages\ViewPowerMonitoring::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Data is created by system only
    }

    public static function canDelete($record): bool
    {
        return false; // Don't allow deletion
    }

    public static function canEdit($record): bool
    {
        return false; // Read-only resource
    }
}
