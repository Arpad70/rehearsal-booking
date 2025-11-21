<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeviceResource\Pages;
use App\Filament\Resources\DeviceResource\RelationManagers;
use App\Models\Device;
use App\Services\DeviceHealthService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DeviceResource extends Resource
{
    protected static ?string $model = Device::class;

    protected static ?string $navigationIcon = 'heroicon-o-server';

    protected static ?string $navigationGroup = 'IoT & Hardware';

    protected static ?string $navigationLabel = 'Zařízení';

    protected static ?string $modelLabel = 'Zařízení';

    protected static ?string $pluralModelLabel = 'Zařízení';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Základní informace')
                    ->schema([
                        Forms\Components\Select::make('type')
                            ->label('Typ zařízení')
                            ->required()
                            ->options([
                                'shelly' => '⚡ Shelly Pro EM',
                                'qr_reader' => '📱 QR Reader (Entry E)',
                                'keypad' => '🔢 RFID Keypad',
                                'camera' => '📹 IP Camera',
                                'mixer' => '🎵 Audio Mixer',
                                'lock' => '🔒 Smart Lock',
                                'reader' => '🎫 Card Reader',
                            ])
                            ->native(false)
                            ->searchable(),

                        Forms\Components\TextInput::make('ip')
                            ->label('IP adresa:port')
                            ->helperText('Například: 172.17.0.1:9101')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Select::make('room_id')
                            ->label('Místnost')
                            ->relationship('room', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ])->columns(3),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('meta.name')
                            ->label('Název zařízení')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meta.model')
                            ->label('Model')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('meta.firmware')
                            ->label('Firmware')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('meta.port')
                            ->label('Port')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535),

                        Forms\Components\Textarea::make('meta.description')
                            ->label('Popis')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('meta.enabled')
                            ->label('Aktivní')
                            ->default(true),
                    ])->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Device $record): string => $record->ip),

                Tables\Columns\TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'shelly' => 'success',
                        'qr_reader' => 'primary',
                        'keypad' => 'warning',
                        'camera' => 'info',
                        'mixer' => 'danger',
                        default => 'gray',
                    })
                    ->icon(fn (string $state): string => match ($state) {
                        'shelly' => 'heroicon-o-bolt',
                        'qr_reader' => 'heroicon-o-qr-code',
                        'keypad' => 'heroicon-o-numbered-list',
                        'camera' => 'heroicon-o-camera',
                        'mixer' => 'heroicon-o-musical-note',
                        'lock' => 'heroicon-o-lock-closed',
                        'reader' => 'heroicon-o-identification',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'shelly' => 'Shelly',
                        'qr_reader' => 'QR Reader',
                        'keypad' => 'Keypad',
                        'camera' => 'Camera',
                        'mixer' => 'Mixer',
                        'lock' => 'Lock',
                        'reader' => 'Reader',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('room.name')
                    ->label('Místnost')
                    ->sortable()
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\IconColumn::make('status')
                    ->label('Stav')
                    ->getStateUsing(function (Device $record): string {
                        $lastCheck = $record->healthChecks()
                            ->latest('checked_at')
                            ->first();
                        
                        if (!$lastCheck) return 'unknown';
                        
                        if ($lastCheck->checked_at->diffInMinutes(now()) > 10) {
                            return 'unknown';
                        }
                        
                        return $lastCheck->status;
                    })
                    ->icon(fn (string $state): string => match($state) {
                        'online' => 'heroicon-o-check-circle',
                        'offline' => 'heroicon-o-x-circle',
                        'error' => 'heroicon-o-exclamation-circle',
                        'degraded' => 'heroicon-o-exclamation-triangle',
                        default => 'heroicon-o-question-mark-circle',
                    })
                    ->color(fn (string $state): string => match($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        'error' => 'danger',
                        'degraded' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('lastCheck.response_time_ms')
                    ->label('Odezva')
                    ->suffix(' ms')
                    ->sortable()
                    ->color(fn ($state): string => match(true) {
                        !$state => 'gray',
                        $state < 50 => 'success',
                        $state < 100 => 'info',
                        $state < 200 => 'warning',
                        default => 'danger',
                    })
                    ->getStateUsing(function (Device $record) {
                        $lastCheck = $record->healthChecks()
                            ->latest('checked_at')
                            ->first();
                        return $lastCheck?->response_time_ms;
                    }),

                Tables\Columns\TextColumn::make('lastCheck.checked_at')
                    ->label('Poslední kontrola')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->since()
                    ->getStateUsing(function (Device $record) {
                        return $record->healthChecks()
                            ->latest('checked_at')
                            ->first()
                            ?->checked_at;
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Aktualizováno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        'shelly' => 'Shelly Pro EM',
                        'qr_reader' => 'QR Reader',
                        'keypad' => 'RFID Keypad',
                        'camera' => 'IP Camera',
                        'mixer' => 'Audio Mixer',
                        'lock' => 'Smart Lock',
                        'reader' => 'Card Reader',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('room_id')
                    ->label('Místnost')
                    ->relationship('room', 'name')
                    ->multiple(),

                Tables\Filters\Filter::make('online')
                    ->label('Pouze online')
                    ->query(fn (Builder $query): Builder => $query->whereHas('healthChecks', function($q) {
                        $q->online()->recent(10);
                    })),

                Tables\Filters\Filter::make('offline')
                    ->label('Pouze offline')
                    ->query(fn (Builder $query): Builder => $query->whereDoesntHave('healthChecks', function($q) {
                        $q->online()->recent(10);
                    })),
            ])
            ->actions([
                Tables\Actions\Action::make('healthCheck')
                    ->label('Otestovat')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->requiresConfirmation(false)
                    ->action(function (Device $record) {
                        $healthService = app(DeviceHealthService::class);
                        $result = $healthService->performHealthCheck($record);
                        
                        if ($result['status'] === 'online') {
                            Notification::make()
                                ->success()
                                ->title('Zařízení je online')
                                ->body("Odezva: {$result['response_time_ms']} ms")
                                ->send();
                        } else {
                            Notification::make()
                                ->danger()
                                ->title('Zařízení je offline')
                                ->body($result['details']['message'] ?? 'Neznámá chyba')
                                ->send();
                        }
                    }),

                Tables\Actions\EditAction::make(),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('healthCheck')
                        ->label('Otestovat vybrané')
                        ->icon('heroicon-o-arrow-path')
                        ->color('info')
                        ->action(function (Collection $records) {
                            $healthService = app(DeviceHealthService::class);
                            $online = 0;
                            $offline = 0;
                            
                            foreach ($records as $record) {
                                $result = $healthService->performHealthCheck($record);
                                if ($result['status'] === 'online') {
                                    $online++;
                                } else {
                                    $offline++;
                                }
                            }
                            
                            Notification::make()
                                ->success()
                                ->title('Health check dokončen')
                                ->body("Online: {$online}, Offline: {$offline}")
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\HealthChecksRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDevices::route('/'),
            'create' => Pages\CreateDevice::route('/create'),
            'edit' => Pages\EditDevice::route('/{record}/edit'),
        ];
    }
}
