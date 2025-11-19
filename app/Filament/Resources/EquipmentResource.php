<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EquipmentResource\Pages;
use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static ?string $slug = 'equipment';

    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';

    protected static ?string $navigationGroup = 'Místnosti';

    protected static ?string $navigationLabel = 'Vybavení';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'vybavení';

    protected static ?string $pluralModelLabel = 'vybavení';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📦 Základní informace')
                    ->description('Údaje o vybavení')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('Název vybavení')
                                    ->required()
                                    ->placeholder('Mikrofon, Projektor, atd.')
                                    ->helperText('Jméno vybavení'),

                                Forms\Components\Select::make('category')
                                    ->label('Kategorie')
                                    ->options(Equipment::getCategories())
                                    ->required()
                                    ->native(false),

                                Forms\Components\TextInput::make('model')
                                    ->label('Model')
                                    ->placeholder('Model nebo označení'),

                                Forms\Components\TextInput::make('serial_number')
                                    ->label('Sériové číslo')
                                    ->placeholder('Unikátní identifikátor'),
                            ]),

                        Forms\Components\Textarea::make('description')
                            ->label('Popis')
                            ->placeholder('Bližší popis vybavení...')
                            ->columnSpanFull()
                            ->rows(2),
                    ]),

                Forms\Components\Section::make('📊 Množství a stav')
                    ->description('Počet kusů a kritičnost')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('quantity_available')
                                    ->label('Dostupné kusy')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(0)
                                    ->step(0.01),

                                Forms\Components\Toggle::make('is_critical')
                                    ->label('Kritické vybavení')
                                    ->helperText('Je toto vybavení kritické pro funkci místnosti?'),

                                Forms\Components\TextInput::make('location')
                                    ->label('Umístění')
                                    ->placeholder('Úschovna, Na stěně, atd.')
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Section::make('📅 Údržba a záruky')
                    ->description('Datum nákupu, záruky a údržby')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\DatePicker::make('purchase_date')
                                    ->label('Datum nákupu'),

                                Forms\Components\DatePicker::make('warranty_expiry')
                                    ->label('Konec záruky'),

                                Forms\Components\Textarea::make('maintenance_notes')
                                    ->label('Poznámky k údržbě')
                                    ->placeholder('Intervaly údržby, poslední kontrola, atd.')
                                    ->columnSpanFull()
                                    ->rows(2),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Vybavení')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('category')
                    ->label('Kategorie')
                    ->formatStateUsing(fn(string $state): string => Equipment::getCategories()[$state] ?? $state)
                    ->color(fn(string $state): string => match ($state) {
                        'audio' => 'info',
                        'video' => 'warning',
                        'furniture' => 'gray',
                        'climate' => 'success',
                        'lighting' => 'warning',
                        default => 'gray',
                    })
                    ->sortable(),

                TextColumn::make('model')
                    ->label('Model')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('quantity_available')
                    ->label('Kusy')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_critical')
                    ->label('Kritické')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('warranty_expiry')
                    ->label('Záruka do')
                    ->date('d.m.Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('Kategorie')
                    ->options(Equipment::getCategories()),

                Tables\Filters\TernaryFilter::make('is_critical')
                    ->label('Kritické vybavení'),
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
            ->defaultSort('name')
            ->paginated([25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEquipment::route('/'),
            'create' => Pages\CreateEquipment::route('/create'),
            'edit' => Pages\EditEquipment::route('/{record}/edit'),
        ];
    }
}
