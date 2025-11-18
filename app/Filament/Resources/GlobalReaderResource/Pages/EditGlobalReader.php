<?php

namespace App\Filament\Resources\GlobalReaderResource\Pages;

use App\Filament\Resources\GlobalReaderResource;
use App\Models\GlobalReader;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;

class EditGlobalReader extends EditRecord
{
    protected static string $resource = GlobalReaderResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('📍 Informace o globálním čtečce')
                    ->description('Globální čtečka dostupná pro více služeb (vchod, servis, admin)')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('reader_name')
                                    ->label('Jméno čtečky')
                                    ->required()
                                    ->placeholder('MainEntrance-01')
                                    ->helperText('Unikátní identifikátor čtečky'),

                                Select::make('access_type')
                                    ->label('Typ přístupu')
                                    ->options([
                                        'entrance' => '🚪 Hlavní vchod',
                                        'service' => '🔧 Servisní přístup',
                                        'admin' => '👨‍💼 Administrátorský přístup',
                                    ])
                                    ->required()
                                    ->disabled(fn ($record) => $record !== null),
                            ]),

                        Toggle::make('enabled')
                            ->label('Aktivní')
                            ->default(true)
                            ->helperText('Zapnutá čtečka je dostupná pro ověřování'),
                    ])
                    ->columnSpan('full'),

                Section::make('🌐 Síťové nastavení')
                    ->description('Připojení k čtečce zařízení')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('reader_ip')
                                    ->label('IP adresa')
                                    ->required()
                                    ->ipv4()
                                    ->placeholder('192.168.1.200')
                                    ->helperText('IP adresa čtečky v síti'),

                                TextInput::make('reader_port')
                                    ->label('Port')
                                    ->required()
                                    ->numeric()
                                    ->default(8080)
                                    ->minValue(1)
                                    ->maxValue(65535),

                                TextInput::make('reader_token')
                                    ->label('Bezpečnostní token')
                                    ->required()
                                    ->password()
                                    ->revealable()
                                    ->helperText('Bearer token pro autentifikaci'),
                            ]),
                    ])
                    ->columnSpan('full'),

                Section::make('⏰ Nastavení přístupu')
                    ->description('Časová okna a podmínky přístupu')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('access_window_before_minutes')
                                    ->label('Přístup před začátkem (minuty)')
                                    ->numeric()
                                    ->default(15)
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->helperText('Kolik minut před začátkem je povolen přístup'),

                                TextInput::make('access_window_after_minutes')
                                    ->label('Přístup po konci (minuty)')
                                    ->numeric()
                                    ->default(15)
                                    ->minValue(0)
                                    ->maxValue(120)
                                    ->helperText('Kolik minut po skončení je povolen přístup'),

                                Toggle::make('allow_multiple_access')
                                    ->label('Povolit vícenásobný přístup')
                                    ->helperText('Umožní více osob přístup s jedním QR kódem')
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->columnSpan('full'),

                Section::make('🔓 Konfigurace zámku')
                    ->description('Nastavení odemykacího mechanismu')
                    ->schema([
                        Select::make('door_lock_type')
                            ->label('Typ zámku')
                            ->options([
                                'relay' => '🔌 Relay (GPIO/Arduino/Shelly)',
                                'api' => '📡 Smart Lock API',
                                'webhook' => '🪝 Webhook (Home Assistant)',
                            ])
                            ->required()
                            ->live(),

                        // Relay configuration
                        Grid::make(2)
                            ->visible(fn ($get) => $get('door_lock_type') === 'relay')
                            ->schema([
                                TextInput::make('door_lock_config.url')
                                    ->label('URL relaye')
                                    ->url()
                                    ->placeholder('http://192.168.1.100:8080/relay/{pin}/on')
                                    ->helperText('Placeholder {pin} bude nahrazen'),

                                TextInput::make('door_lock_config.pin')
                                    ->label('GPIO pin')
                                    ->numeric()
                                    ->default(2)
                                    ->minValue(0),

                                TextInput::make('door_lock_config.duration')
                                    ->label('Doba otevření (sec)')
                                    ->numeric()
                                    ->default(5)
                                    ->minValue(1)
                                    ->maxValue(60),
                            ]),

                        // API configuration
                        Grid::make(2)
                            ->visible(fn ($get) => $get('door_lock_type') === 'api')
                            ->schema([
                                TextInput::make('door_lock_config.api_url')
                                    ->label('API URL')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://api.smartlock.com/unlock'),

                                TextInput::make('door_lock_config.api_key')
                                    ->label('API klíč')
                                    ->password()
                                    ->revealable()
                                    ->required(),

                                TextInput::make('door_lock_config.lock_id')
                                    ->label('Lock ID')
                                    ->required(),

                                TextInput::make('door_lock_config.duration')
                                    ->label('Doba otevření (sec)')
                                    ->numeric()
                                    ->default(5),
                            ]),

                        // Webhook configuration
                        Grid::make(2)
                            ->visible(fn ($get) => $get('door_lock_type') === 'webhook')
                            ->schema([
                                TextInput::make('door_lock_config.webhook_url')
                                    ->label('Webhook URL')
                                    ->url()
                                    ->required(),

                                TextInput::make('door_lock_config.secret')
                                    ->label('Secret key (HMAC)')
                                    ->password()
                                    ->revealable()
                                    ->required(),

                                TextInput::make('door_lock_config.duration')
                                    ->label('Doba otevření (sec)')
                                    ->numeric()
                                    ->default(5),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('test')
                ->label('🧪 Test připojení')
                ->icon('heroicon-o-wifi')
                ->size(ActionSize::Medium)
                ->color('info')
                ->action(function (GlobalReader $record) {
                    $result = $record->testConnection();
                    if ($result['success']) {
                        $this->notify('success', $result['message']);
                    } else {
                        $this->notify('danger', $result['message']);
                    }
                }),

            Actions\Action::make('unlock')
                ->label('🔓 Testuj odemčení')
                ->icon('heroicon-o-lock-open')
                ->size(ActionSize::Medium)
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (GlobalReader $record) {
                    try {
                        $result = app(\App\Services\DoorLockService::class)->unlockGlobalReader($record);
                        if ($result['success']) {
                            $this->notify('success', 'Dveře odemčeny na ' . $result['duration'] . ' sekund');
                        } else {
                            $this->notify('danger', $result['message']);
                        }
                    } catch (\Exception $e) {
                        $this->notify('danger', 'Chyba: ' . $e->getMessage());
                    }
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return '✅ Globální čtečka uložena úspěšně';
    }
}
