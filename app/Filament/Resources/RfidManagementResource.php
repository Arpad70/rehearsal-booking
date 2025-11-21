<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RfidManagementResource\Pages;
use App\Http\Controllers\Api\RfidController;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;

class RfidManagementResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-radio';

    protected static ?string $navigationGroup = 'Správa vybavení';

    protected static ?string $navigationLabel = 'RFID Správa';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'RFID Tag';

    protected static ?string $pluralModelLabel = 'RFID Tagy';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Identifikační Tag')
                    ->description('Přiřaďte RFID nebo NFC tag k vybavení')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Název vybavení')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategorie')
                            ->relationship('category', 'name')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Select::make('tag_type')
                            ->label('Typ identifikace')
                            ->options([
                                'rfid' => '📡 RFID',
                                'nfc' => '📱 NFC',
                            ])
                            ->required()
                            ->live()
                            ->helperText('RFID: větší dosah, NFC: kratší dosah, bezpečnější'),

                        Forms\Components\TextInput::make('tag_id')
                            ->label(fn (Forms\Get $get) => match($get('tag_type')) {
                                'nfc' => '📱 NFC Tag ID',
                                'rfid' => '📡 RFID Tag ID',
                                default => '🏷️ Tag ID',
                            })
                            ->required()
                            ->unique(table: 'equipment', column: 'tag_id', ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText(fn (Forms\Get $get) => match($get('tag_type')) {
                                'nfc' => 'Přiložte NFC tag k čtečce',
                                'rfid' => 'Přiložte RFID tag ke čtečce',
                                default => 'Nejdřív vyberte typ',
                            })
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('scan')
                                    ->label('Skenovat')
                                    ->icon(fn (Forms\Get $get) => match($get('tag_type')) {
                                        'nfc' => 'heroicon-o-device-phone-mobile',
                                        default => 'heroicon-o-signal',
                                    })
                                    ->color('primary')
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        $type = $get('tag_type') === 'nfc' ? 'NFC' : 'RFID';
                                        Notification::make()
                                            ->title("Přiložte {$type} tag ke čtečce")
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

                        Forms\Components\Placeholder::make('scan_status')
                            ->label('Stav čtečky')
                            ->content(fn() => self::getReaderStatus()),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->whereNotNull('tag_id'))
            ->columns([
                Tables\Columns\TextColumn::make('tag_type')
                    ->label('Typ')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'rfid' => '📡 RFID',
                        'nfc' => '📱 NFC',
                        default => '🏷️ Tag',
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'rfid' => 'info',
                        'nfc' => 'success',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tag_id')
                    ->label('Tag ID')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Tag ID zkopírován')
                    ->copyMessageDuration(1500)
                    ->description(fn (Equipment $record) => $record->getTagTypeLabel()),

                Tables\Columns\TextColumn::make('name')
                    ->label('Vybavení')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Equipment $record) => $record->model),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategorie')
                    ->badge()
                    ->formatStateUsing(fn ($state, $record) => 
                        $record->relationLoaded('category') && $record->getRelation('category') 
                            ? ($record->getRelation('category')->icon . ' ' . $state) 
                            : $state
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'available' => 'success',
                        'in_use' => 'warning',
                        'maintenance' => 'info',
                        'repair' => 'danger',
                        'retired' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'available' => '✅ Dostupné',
                        'in_use' => '🔵 Používané',
                        'maintenance' => '🛠️ Údržba',
                        'repair' => '🔧 V opravě',
                        'retired' => '❌ Vyřazeno',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->label('Umístění')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\IconColumn::make('is_critical')
                    ->label('Kritické')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Přidáno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tag_type')
                    ->label('Typ tagu')
                    ->options([
                        'rfid' => '📡 RFID',
                        'nfc' => '📱 NFC',
                    ])
                    ->placeholder('Všechny typy'),

                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategorie')
                    ->relationship('category', 'name')
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Stav')
                    ->options([
                        'available' => '✅ Dostupné',
                        'in_use' => '🔵 Používané',
                        'maintenance' => '🛠️ Údržba',
                        'repair' => '🔧 V opravě',
                        'retired' => '❌ Vyřazeno',
                    ]),

                Tables\Filters\TernaryFilter::make('is_critical')
                    ->label('Pouze kritické'),
            ])
            ->actions([
                Tables\Actions\Action::make('test_scan')
                    ->label('Test čtení')
                    ->icon('heroicon-o-signal')
                    ->color('info')
                    ->action(function (Equipment $record) {
                        try {
                            // Přímé volání API controlleru
                            $request = Request::create('/api/v1/rfid/read', 'POST', [
                                'tag_id' => $record->tag_id,
                                'tag_type' => $record->tag_type,
                            ]);
                            
                            $controller = app(RfidController::class);
                            $response = $controller->read($request);
                            $data = json_decode($response->getContent(), true);

                            if ($response->getStatusCode() === 200 && isset($data['equipment'])) {
                                Notification::make()
                                    ->title('Tag načten')
                                    ->body("Vybavení: {$data['equipment']['name']} ({$record->getTagTypeLabel()})")
                                    ->success()
                                    ->send();
                            } else {
                                throw new \Exception('API vrátila chybu');
                            }
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Chyba čtení tagu')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),

                Tables\Actions\Action::make('remove_tag')
                    ->label('Odebrat tag')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Odebrat identifikační tag')
                    ->modalDescription('Opravdu chcete odebrat identifikační tag z tohoto vybavení?')
                    ->modalSubmitActionLabel('Odebrat')
                    ->action(function (Equipment $record) {
                        $record->update([
                            'tag_id' => null,
                            'tag_type' => null,
                        ]);
                        
                        Notification::make()
                            ->title('Tag odebrán')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\EditAction::make()
                    ->label('Upravit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Odebrat tagy')
                        ->modalHeading('Odebrat identifikační tagy')
                        ->modalDescription('Opravdu chcete odebrat identifikační tagy z vybraného vybavení?')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->update([
                                    'tag_id' => null,
                                    'tag_type' => null,
                                ]);
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('export')
                    ->label('Exportovat do Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'available' => 'Dostupné',
                                'in_use' => 'Používané',
                                'maintenance' => 'V údržbě',
                                'damaged' => 'Poškozené',
                                'lost' => 'Ztracené',
                            ])
                            ->placeholder('Všechny'),
                        
                        Forms\Components\TextInput::make('location')
                            ->label('Místnost')
                            ->placeholder('Filtrovat podle místnosti'),
                        
                        Forms\Components\Select::make('tag_type')
                            ->label('Typ tagu')
                            ->options([
                                'rfid' => 'RFID',
                                'nfc' => 'NFC',
                            ])
                            ->placeholder('Všechny typy'),
                        
                        Forms\Components\Toggle::make('is_critical')
                            ->label('Pouze kritické vybavení'),
                    ])
                    ->action(function (array $data) {
                        return response()->download(
                            (new \App\Exports\EquipmentExport($data))->store('equipment_export.xlsx', 'local', \Maatwebsite\Excel\Excel::XLSX),
                            'vybaveni_' . now()->format('Y-m-d_His') . '.xlsx'
                        );
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRfidManagement::route('/'),
            'create' => Pages\CreateRfidManagement::route('/create'),
            'edit' => Pages\EditRfidManagement::route('/{record}/edit'),
            'reader' => Pages\RfidReaderSetup::route('/reader-setup'),
            'inventory' => Pages\InventoryScanner::route('/inventory'),
        ];
    }

    protected static function getReaderStatus(): string
    {
        try {
            // Přímé volání API controlleru
            $controller = app(RfidController::class);
            $response = $controller->readerStatus();
            $data = json_decode($response->getContent(), true);
            
            if ($response->getStatusCode() === 200 && isset($data['connected']) && $data['connected']) {
                return '🟢 Čtečka připojena';
            }
        } catch (\Exception $e) {
            // Ignorovat chybu
        }

        return '🔴 Čtečka není připojena';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereNotNull('tag_id')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}
