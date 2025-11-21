<?php

namespace App\Filament\Resources\ServiceAccessResource\Pages;

use App\Filament\Resources\ServiceAccessResource;
use App\Models\ServiceAccess;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Filament\Support\Enums\ActionSize;
use Filament\Notifications\Notification;

class EditServiceAccess extends EditRecord
{
    protected static string $resource = ServiceAccessResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('👤 Údaje o přístupu')
                    ->description('Informace o osobě se servisním přístupem')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextInput::make('service_person_name')
                                    ->label('Jméno')
                                    ->required()
                                    ->placeholder('Jan Novák')
                                    ->helperText('Jméno osoby se servisním přístupem'),

                                Select::make('access_type')
                                    ->label('Typ přístupu')
                                    ->options([
                                        'cleaning' => '🧹 Čištění',
                                        'maintenance' => '🔧 Údržba',
                                        'admin' => '👨‍💼 Administrace',
                                    ])
                                    ->required()
                                    ->live(),

                                TextInput::make('email')
                                    ->label('Email')
                                    ->email()
                                    ->helperText('Kde se bude odesílat QR kód'),

                                TextInput::make('phone')
                                    ->label('Telefon')
                                    ->tel()
                                    ->helperText('Kontaktní číslo'),
                            ]),
                    ])
                    ->columnSpan('full'),

                Section::make('⏰ Platnost přístupu')
                    ->description('Časové období, kdy je přístup platný')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                DateTimePicker::make('valid_from')
                                    ->label('Platný od')
                                    ->required()
                                    ->minDate(now())
                                    ->helperText('Kdy začíná být přístup platný'),

                                DateTimePicker::make('valid_until')
                                    ->label('Platný do')
                                    ->required()
                                    ->minDate(now())
                                    ->helperText('Kdy skončí platnost přístupu'),

                                TextInput::make('access_limit')
                                    ->label('Počet přístupů')
                                    ->numeric()
                                    ->helperText('Kolikrát lze QR kód použít (prázdné = neomezeno)'),

                                Toggle::make('enabled')
                                    ->label('Aktivní')
                                    ->default(true)
                                    ->helperText('Vypnutý přístup nebude fungovat'),
                            ]),
                    ])
                    ->columnSpan('full'),

                Section::make('🚪 Přístup do místností')
                    ->description('Místnosti, do kterých je povolený přístup')
                    ->schema([
                        Toggle::make('all_rooms')
                            ->label('Přístup do všech místností')
                            ->live()
                            ->helperText('Pokud vypnete, můžete vybrat konkrétní místnosti'),

                        CheckboxList::make('allowed_rooms')
                            ->label('Vybrané místnosti')
                            ->options(\App\Models\Room::pluck('name', 'id'))
                            ->visible(fn ($get) => !$get('all_rooms'))
                            ->helperText('Fyzické místnosti, ke kterým je přístup povolen'),
                    ])
                    ->columnSpan('full'),

                Section::make('📋 Poznámky a omezení')
                    ->description('Dodatečné informace')
                    ->schema([
                        TextInput::make('notes')
                            ->label('Poznámky')
                            ->placeholder('Např: Jen během pracovní doby...')
                            ->columnSpanFull()
                            ->helperText('Interní poznámky pro administrátory'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('revocation_reason')
                                    ->label('Důvod zrušení')
                                    ->disabled()
                                    ->helperText('Vyplní se automaticky při zrušení'),

                                DateTimePicker::make('revoked_at')
                                    ->label('Zrušeno')
                                    ->disabled()
                                    ->helperText('Čas zrušení přístupu'),
                            ]),
                    ])
                    ->columnSpan('full'),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_qr')
                ->label('📱 Vygeneruj QR kód')
                ->icon('heroicon-o-qr-code')
                ->size(ActionSize::Medium)
                ->color('success')
                ->action(function (ServiceAccess $record) {
                    try {
                        // Odeslat QR kód na email
                        \Illuminate\Support\Facades\Mail::to($record->email)
                            ->queue(new \App\Mail\ServiceAccessCodeMail($record));
                        Notification::make()
                            ->success()
                            ->title('QR kód byl odeslán na email: ' . $record->email)
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Chyba: ' . $e->getMessage())
                            ->send();
                    }
                }),

            Actions\Action::make('revoke')
                ->label('❌ Zruš přístup')
                ->icon('heroicon-o-no-symbol')
                ->size(ActionSize::Medium)
                ->color('danger')
                ->requiresConfirmation()
                ->form([
                    TextInput::make('reason')
                        ->label('Důvod zrušení')
                        ->required()
                        ->placeholder('Např: Ukončení pracovní smlouvy'),
                ])
                ->action(function (ServiceAccess $record, array $data) {
                    $record->update([
                        'revoked_at' => now(),
                        'revocation_reason' => $data['reason'],
                        'enabled' => false,
                    ]);
                    Notification::make()
                        ->success()
                        ->title('Přístup byl zrušen')
                        ->send();
                }),

            Actions\DeleteAction::make(),
        ];
    }

    protected function getSavedNotificationMessage(): ?string
    {
        return '✅ Servisní přístup uložen úspěšně';
    }
}
