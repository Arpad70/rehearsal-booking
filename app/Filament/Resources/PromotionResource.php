<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PromotionResource\Pages;
use App\Filament\Resources\PromotionResource\RelationManagers;
use App\Models\Promotion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PromotionResource extends Resource
{
    protected static ?string $model = Promotion::class;

    protected static ?string $navigationIcon = 'heroicon-o-gift';

    protected static ?string $navigationLabel = 'Propagace';

    protected static ?string $modelLabel = 'Propagace';

    protected static ?string $pluralModelLabel = 'Propagace';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Základní informace')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Název propagace')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('description')
                            ->label('Popis')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),

                        Forms\Components\Select::make('type')
                            ->label('Typ propagace')
                            ->required()
                            ->options([
                                'registration_discount' => 'Sleva při registraci',
                                'event_discount' => 'Sleva na akci',
                                'general_info' => 'Obecná informace',
                                'announcement' => 'Oznámení',
                            ])
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('priority')
                            ->label('Priorita')
                            ->numeric()
                            ->default(1)
                            ->helperText('Vyšší číslo = vyšší priorita')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Sleva')
                    ->schema([
                        Forms\Components\TextInput::make('discount_code')
                            ->label('Kód slevy')
                            ->maxLength(50),

                        Forms\Components\TextInput::make('discount_percentage')
                            ->label('Sleva v procentech')
                            ->numeric()
                            ->suffix('%')
                            ->minValue(0)
                            ->maxValue(100),

                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Sleva v Kč')
                            ->numeric()
                            ->suffix('Kč')
                            ->minValue(0),
                    ])
                    ->columns(3)
                    ->visible(fn (Forms\Get $get) => in_array($get('type'), ['registration_discount', 'event_discount'])),

                Forms\Components\Section::make('Zobrazení')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktivní')
                            ->default(true)
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('is_permanent')
                            ->label('Trvalá propagace')
                            ->helperText('Zobrazovat bez časového omezení')
                            ->reactive()
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('start_date')
                            ->label('Začátek platnosti')
                            ->hidden(fn (Forms\Get $get) => $get('is_permanent'))
                            ->columnSpan(1),

                        Forms\Components\DateTimePicker::make('end_date')
                            ->label('Konec platnosti')
                            ->hidden(fn (Forms\Get $get) => $get('is_permanent'))
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('max_displays')
                            ->label('Max. počet zobrazení')
                            ->numeric()
                            ->helperText('Nechte prázdné pro neomezené zobrazení')
                            ->columnSpan(1),

                        Forms\Components\Toggle::make('show_once_per_session')
                            ->label('Pouze 1x za session')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Cílení')
                    ->schema([
                        Forms\Components\CheckboxList::make('target_audience')
                            ->label('Cílová skupina')
                            ->options([
                                'guest' => 'Nepřihlášení uživatelé',
                                'registered' => 'Registrovaní uživatelé',
                                'all' => 'Všichni',
                            ])
                            ->default(['all'])
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Vzhled a akce')
                    ->schema([
                        Forms\Components\TextInput::make('image_url')
                            ->label('URL obrázku')
                            ->url()
                            ->maxLength(500)
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('button_text')
                            ->label('Text tlačítka')
                            ->maxLength(100)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('button_url')
                            ->label('URL tlačítka')
                            ->url()
                            ->maxLength(500)
                            ->columnSpan(1),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Název')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('type')
                    ->label('Typ')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'registration_discount' => 'success',
                        'event_discount' => 'warning',
                        'general_info' => 'info',
                        'announcement' => 'gray',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'registration_discount' => 'Sleva při registraci',
                        'event_discount' => 'Sleva na akci',
                        'general_info' => 'Info',
                        'announcement' => 'Oznámení',
                        default => $state,
                    }),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('priority')
                    ->label('Priorita')
                    ->sortable()
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('start_date')
                    ->label('Začátek')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('end_date')
                    ->label('Konec')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('views_count')
                    ->label('Zobrazení')
                    ->counts('views')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('priority', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Typ')
                    ->options([
                        'registration_discount' => 'Sleva při registraci',
                        'event_discount' => 'Sleva na akci',
                        'general_info' => 'Obecná informace',
                        'announcement' => 'Oznámení',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktivní')
                    ->placeholder('Vše')
                    ->trueLabel('Pouze aktivní')
                    ->falseLabel('Pouze neaktivní'),

                Tables\Filters\Filter::make('active_now')
                    ->label('Aktuálně platné')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('is_active', true)
                        ->where(function ($q) {
                            $q->where('is_permanent', true)
                              ->orWhere(function ($q2) {
                                  $q2->where('start_date', '<=', now())
                                     ->where('end_date', '>=', now());
                              });
                        })
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\PromotionViewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit' => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
