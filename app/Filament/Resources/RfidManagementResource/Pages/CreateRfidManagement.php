<?php

namespace App\Filament\Resources\RfidManagementResource\Pages;

use App\Filament\Resources\RfidManagementResource;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Actions;

class CreateRfidManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = RfidManagementResource::class;

    protected static string $view = 'filament.resources.rfid-management-resource.pages.create-rfid-management';

    protected static ?string $title = 'Přidat identifikační tag';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Přidat identifikační tag')
                    ->description('Přiřaďte RFID nebo NFC tag k existujícímu vybavení')
                    ->schema([
                        Forms\Components\Select::make('equipment_id')
                            ->label('Vybavení')
                            ->options(
                                Equipment::whereNull('tag_id')
                                    ->orWhere('tag_id', '')
                                    ->get()
                                    ->mapWithKeys(fn ($equipment) => [
                                        $equipment->id => $equipment->name . 
                                            ($equipment->model ? " ({$equipment->model})" : '')
                                    ])
                            )
                            ->searchable()
                            ->required()
                            ->placeholder('Vyberte vybavení bez tagu')
                            ->helperText('Zobrazuje se pouze vybavení bez přiřazeného tagu'),

                        Forms\Components\Select::make('tag_type')
                            ->label('Typ tagu')
                            ->options([
                                'rfid' => '📡 RFID (Radio-Frequency Identification)',
                                'nfc' => '📱 NFC (Near Field Communication)',
                            ])
                            ->required()
                            ->live()
                            ->helperText('Vyberte technologii identifikačního tagu'),

                        Forms\Components\TextInput::make('tag_id')
                            ->label(fn (Forms\Get $get) => match($get('tag_type')) {
                                'nfc' => '📱 NFC Tag ID',
                                'rfid' => '📡 RFID Tag ID',
                                default => '🏷️ Tag ID',
                            })
                            ->required()
                            ->disabled(fn (Forms\Get $get) => !$get('tag_type'))
                            ->maxLength(255)
                            ->helperText(fn (Forms\Get $get) => !$get('tag_type') 
                                ? 'Nejprve vyberte typ tagu' 
                                : 'Přiložte tag ke čtečce nebo zadejte ručně')
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('scan')
                                    ->label('Skenovat')
                                    ->icon(fn (Forms\Get $get) => match($get('tag_type')) {
                                        'nfc' => 'heroicon-o-device-phone-mobile',
                                        default => 'heroicon-o-signal',
                                    })
                                    ->color('primary')
                                    ->disabled(fn (Forms\Get $get) => !$get('tag_type'))
                                    ->action(function (Forms\Get $get) {
                                        Notification::make()
                                            ->title($get('tag_type') === 'nfc' 
                                                ? 'Přiložte NFC tag ke čtečce' 
                                                : 'Přiložte RFID tag ke čtečce')
                                            ->info()
                                            ->send();
                                    })
                            )
                            ->live()
                            ->extraAttributes([
                                'x-data' => '{ 
                                    init() {
                                        window.addEventListener("rfid-scanned", (event) => {
                                            this.$el.value = event.detail.tag;
                                            this.$el.dispatchEvent(new Event("input", { bubbles: true }));
                                        });
                                        window.addEventListener("nfc-scanned", (event) => {
                                            this.$el.value = event.detail.tag;
                                            this.$el.dispatchEvent(new Event("input", { bubbles: true }));
                                        });
                                    }
                                }',
                                'placeholder' => 'Přiložte tag nebo klikněte Skenovat...',
                            ]),
                    ])
                    ->columns(1),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('save')
                        ->label('Přiřadit tag')
                        ->action('create')
                        ->color('primary'),
                    
                    Forms\Components\Actions\Action::make('cancel')
                        ->label('Zrušit')
                        ->url(static::$resource::getUrl('index'))
                        ->color('gray'),
                ]),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();

        $equipment = Equipment::find($data['equipment_id']);
        
        if (!$equipment) {
            Notification::make()
                ->title('Chyba')
                ->body('Vybavení nebylo nalezeno')
                ->danger()
                ->send();
            return;
        }

        // Kontrola duplicity
        if (Equipment::where('tag_id', $data['tag_id'])
            ->where('id', '!=', $equipment->id)
            ->exists()) {
            Notification::make()
                ->title('Chyba')
                ->body('Tento tag je již přiřazen jinému vybavení')
                ->danger()
                ->send();
            return;
        }

        $equipment->tag_id = $data['tag_id'];
        $equipment->tag_type = $data['tag_type'];
        $equipment->save();

        $tagLabel = match($data['tag_type']) {
            'rfid' => '📡 RFID',
            'nfc' => '📱 NFC',
            default => '🏷️',
        };

        Notification::make()
            ->title('Tag přiřazen')
            ->body("{$tagLabel} tag {$data['tag_id']} byl přiřazen k vybavení {$equipment->name}")
            ->success()
            ->send();

        $this->redirect(static::$resource::getUrl('index'));
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Zpět')
                ->url(static::$resource::getUrl('index'))
                ->color('gray'),
        ];
    }
}
