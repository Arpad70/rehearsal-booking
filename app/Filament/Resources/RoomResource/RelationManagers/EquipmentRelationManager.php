<?php

namespace App\Filament\Resources\RoomResource\RelationManagers;

use App\Models\Equipment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class EquipmentRelationManager extends RelationManager
{
    protected static string $relationship = 'equipment';

    protected static ?string $recordTitleAttribute = 'name';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\Select::make('equipment_id')
                            ->label('Vybavení')
                            ->relationship('equipment', 'name')
                            ->options(Equipment::all()->pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('quantity')
                            ->label('Počet kusů v místnosti')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),

                        Forms\Components\Toggle::make('installed')
                            ->label('Nainstalováno')
                            ->default(true),

                        Forms\Components\Select::make('status')
                            ->label('Stav')
                            ->options(Equipment::getStatusOptions())
                            ->default('operational')
                            ->required()
                            ->native(false),

                        Forms\Components\DateTimePicker::make('last_inspection')
                            ->label('Poslední kontrola')
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('condition_notes')
                            ->label('Poznámky k stavu')
                            ->placeholder('Popište stav vybavení...')
                            ->columnSpanFull()
                            ->rows(3),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Vybavení')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Kategorie')
                    ->formatStateUsing(fn(string $state): string => Equipment::getCategories()[$state] ?? $state)
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'audio' => 'info',
                        'video' => 'warning',
                        'furniture' => 'gray',
                        'climate' => 'success',
                        'lighting' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('pivot.quantity')
                    ->label('Počet')
                    ->numeric(),

                Tables\Columns\IconColumn::make('pivot.installed')
                    ->label('Nainstalováno')
                    ->boolean(),

                Tables\Columns\TextColumn::make('pivot.status')
                    ->label('Stav')
                    ->formatStateUsing(fn($state) => Equipment::getStatusOptions()[$state] ?? $state)
                    ->badge()
                    ->colors([
                        'success' => 'operational',
                        'warning' => 'needs_repair',
                        'info' => 'maintenance',
                        'danger' => 'removed',
                    ]),

                Tables\Columns\TextColumn::make('pivot.last_inspection')
                    ->label('Poslední kontrola')
                    ->dateTime('d.m.Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pivot.status')
                    ->label('Stav')
                    ->options(Equipment::getStatusOptions()),

                Tables\Filters\TernaryFilter::make('pivot.installed')
                    ->label('Nainstalováno'),
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
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Přidat vybavení')
                    ->form(fn(Tables\Actions\AttachAction $action): array => [
                        $action->getRecordSelect(),
                        Forms\Components\TextInput::make('quantity')
                            ->label('Počet kusů')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required(),
                        Forms\Components\Toggle::make('installed')
                            ->label('Nainstalováno')
                            ->default(true),
                        Forms\Components\Select::make('status')
                            ->label('Stav')
                            ->options(Equipment::getStatusOptions())
                            ->default('operational')
                            ->required(),
                    ]),
            ]);
    }
}
