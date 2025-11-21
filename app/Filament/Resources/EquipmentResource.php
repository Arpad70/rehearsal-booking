<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';

    protected static ?string $navigationGroup = 'Správa vybavení';

    protected static ?string $navigationLabel = 'Vybavení';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Základní informace')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Název')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(2),

                        Forms\Components\Textarea::make('description')
                            ->label('Popis')
                            ->rows(3)
                            ->columnSpan(2),

                        Forms\Components\Select::make('category_id')
                            ->label('Kategorie')
                            ->relationship('category', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')
                                    ->label('Název kategorie')
                                    ->required(),
                                Forms\Components\TextInput::make('slug')
                                    ->label('Slug')
                                    ->required(),
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Stav')
                            ->options(Equipment::getStatusOptions())
                            ->required()
                            ->default('available'),

                        Forms\Components\TextInput::make('location')
                            ->label('Umístění')
                            ->placeholder('např. Sklad, Zkušebna 1'),

                        Forms\Components\Toggle::make('is_critical')
                            ->label('Kritické vybavení')
                            ->helperText('Označit důležité vybavení, které je nutné sledovat'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Technické údaje')
                    ->schema([
                        Forms\Components\TextInput::make('model')
                            ->label('Model')
                            ->maxLength(255),

                        Forms\Components\TextInput::make('serial_number')
                            ->label('Sériové číslo')
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        Forms\Components\Select::make('tag_type')
                            ->label('Typ identifikace')
                            ->options([
                                'rfid' => '📡 RFID (Radio-Frequency Identification)',
                                'nfc' => '📱 NFC (Near Field Communication)',
                            ])
                            ->placeholder('Vyberte typ tagu')
                            ->helperText('RFID: větší dosah (až 10m), NFC: kratší dosah (do 10cm), bezpečnější')
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                // Při změně typu můžeme zachovat tag_id
                            }),

                        Forms\Components\TextInput::make('tag_id')
                            ->label(fn (Forms\Get $get) => match($get('tag_type')) {
                                'nfc' => '📱 NFC Tag ID',
                                'rfid' => '📡 RFID Tag ID',
                                default => '🏷️ Tag ID',
                            })
                            ->unique(table: 'equipment', column: 'tag_id', ignoreRecord: true)
                            ->helperText(fn (Forms\Get $get) => match($get('tag_type')) {
                                'nfc' => 'Přiložte NFC tag k čtečce nebo telefonu',
                                'rfid' => 'Přiložte RFID tag ke čtečce',
                                default => 'Nejdřív vyberte typ identifikace',
                            })
                            ->disabled(fn (Forms\Get $get) => !$get('tag_type'))
                            ->dehydrated()
                            ->maxLength(255)
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('scan_tag')
                                    ->label('Načíst z čtečky')
                                    ->icon(fn (Forms\Get $get) => match($get('tag_type')) {
                                        'nfc' => 'heroicon-o-device-phone-mobile',
                                        default => 'heroicon-o-signal',
                                    })
                                    ->color('primary')
                                    ->disabled(fn (Forms\Get $get) => !$get('tag_type'))
                                    ->extraAttributes([
                                        'x-on:click' => "\$dispatch('open-rfid-scanner')",
                                    ])
                                    ->action(function (Forms\Set $set, Forms\Get $get) {
                                        // Akce se vykoná přes JavaScript
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
                                'placeholder' => fn (Forms\Get $get) => match($get('tag_type')) {
                                    'nfc' => 'Přiložte NFC tag nebo klikněte Načíst...',
                                    'rfid' => 'Přiložte RFID tag nebo klikněte Načíst...',
                                    default => 'Nejdřív vyberte typ identifikace...',
                                },
                            ]),

                        Forms\Components\Placeholder::make('migration_notice')
                            ->label('💡 Tip')
                            ->content('Můžete kdykoli změnit typ tagu (RFID ↔ NFC). Vaše data zůstanou zachována.')
                            ->hidden(fn (Forms\Get $get) => !$get('tag_id')),

                        Forms\Components\TextInput::make('quantity_available')
                            ->label('Dostupné množství')
                            ->numeric()
                            ->default(1)
                            ->minValue(0)
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Nákup a záruka')
                    ->schema([
                        Forms\Components\DatePicker::make('purchase_date')
                            ->label('Datum nákupu')
                            ->native(false),

                        Forms\Components\TextInput::make('purchase_price')
                            ->label('Pořizovací cena')
                            ->numeric()
                            ->prefix('Kč')
                            ->minValue(0),

                        Forms\Components\DatePicker::make('warranty_expiry')
                            ->label('Konec záruky')
                            ->native(false)
                            ->helperText('Automaticky se zvýrazní, pokud vyprší'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Údržba')
                    ->schema([
                        Forms\Components\DatePicker::make('last_maintenance')
                            ->label('Poslední údržba')
                            ->native(false),

                        Forms\Components\DatePicker::make('next_maintenance')
                            ->label('Další údržba')
                            ->native(false)
                            ->helperText('Systém upozorní, když se blíží termín'),

                        Forms\Components\Textarea::make('maintenance_notes')
                            ->label('Poznámky k údržbě')
                            ->rows(3)
                            ->columnSpan(2),
                    ])
                    ->columns(2)
                    ->collapsible(),
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
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategorie')
                    ->badge()
                    ->formatStateUsing(function ($state, $record) {
                        if (!$record || !$record->relationLoaded('category') || !$record->category) {
                            return $state ?? '-';
                        }
                        return ($record->category->icon ?? '') . ' ' . ($state ?? $record->category->name ?? '-');
                    })
                    ->color(fn ($record) => $record->category ? 'primary' : 'gray')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Equipment::getStatusOptions()[$state] ?? $state)
                    ->color(fn (string $state): string => match($state) {
                        'available' => 'success',
                        'in_use' => 'info',
                        'maintenance' => 'warning',
                        'repair' => 'danger',
                        'retired' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('location')
                    ->label('Umístění')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('serial_number')
                    ->label('S/N')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('rfid_tag')
                    ->label('RFID')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('quantity_available')
                    ->label('Množství')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('is_critical')
                    ->label('Kritické')
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('danger')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('next_maintenance')
                    ->label('Další údržba')
                    ->date('d.m.Y')
                    ->sortable()
                    ->color(fn ($record) => $record->needsMaintenance() ? 'danger' : 'success')
                    ->icon(fn ($record) => $record->needsMaintenance() ? 'heroicon-o-exclamation-triangle' : null)
                    ->toggleable(),

                Tables\Columns\IconColumn::make('warranty_expiry')
                    ->label('Záruka')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->hasValidWarranty())
                    ->trueIcon('heroicon-o-shield-check')
                    ->falseIcon('heroicon-o-shield-exclamation')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('purchase_price')
                    ->label('Cena')
                    ->money('CZK')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategorie')
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Stav')
                    ->options(Equipment::getStatusOptions())
                    ->multiple(),

                Tables\Filters\Filter::make('needs_maintenance')
                    ->label('Potřebuje údržbu')
                    ->query(fn (Builder $query) => $query->whereNotNull('next_maintenance')
                        ->whereDate('next_maintenance', '<=', now())),

                Tables\Filters\Filter::make('is_critical')
                    ->label('Pouze kritické')
                    ->query(fn (Builder $query) => $query->where('is_critical', true)),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'repair')->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }
}
