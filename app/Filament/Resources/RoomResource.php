<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use App\Services\ImageService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Jobs\ToggleShellyJob;
use Illuminate\Http\UploadedFile;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Základní informace')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Název místnosti')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('location')
                            ->label('Umístění')
                            ->maxLength(255)
                            ->placeholder('např. 1. patro, budova A'),
                        Forms\Components\Textarea::make('description')
                            ->label('Popis místnosti')
                            ->maxLength(1000)
                            ->rows(3)
                            ->placeholder('Podrobný popis zkušebny, vybavení, zvláštnosti...')
                            ->helperText('Tento popis se zobrazí na veřejných stránkách')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('size')
                            ->label('Rozměr')
                            ->maxLength(255)
                            ->placeholder('např. Velká (45 m²)')
                            ->helperText('Velikost místnosti pro zobrazení na frontendu'),
                        Forms\Components\TextInput::make('capacity')
                            ->label('Kapacita')
                            ->required()
                            ->numeric()
                            ->default(1)
                            ->minValue(1),
                        Forms\Components\TextInput::make('price_per_hour')
                            ->label('Cena za hodinu (Kč)')
                            ->required()
                            ->numeric()
                            ->default(200)
                            ->minValue(0)
                            ->suffix('Kč')
                            ->helperText('Cena pronájmu za jednu hodinu'),
                        Forms\Components\Toggle::make('enabled')
                            ->label('Místnost aktivní')
                            ->default(true)
                            ->helperText('Zapnutá místnost je dostupná pro rezervace'),
                        Forms\Components\Toggle::make('is_public')
                            ->label('Veřejná místnost')
                            ->default(true)
                            ->helperText('Veřejné místnosti se zobrazují na hlavní stránce'),
                    ])->columns(2),

                Forms\Components\Section::make('Adresa a zobrazení')
                    ->schema([
                        Forms\Components\TextInput::make('address')
                            ->label('Adresa')
                            ->maxLength(255)
                            ->placeholder('např. Hlavní 123, Praha 1')
                            ->helperText('Adresa pro zobrazení na Google mapách'),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('latitude')
                                    ->label('Zeměpisná šířka')
                                    ->numeric()
                                    ->placeholder('50.0755'),
                                Forms\Components\TextInput::make('longitude')
                                    ->label('Zeměpisná délka')
                                    ->numeric()
                                    ->placeholder('14.4378'),
                            ]),
                        Forms\Components\FileUpload::make('image')
                            ->label('Obrázek místnosti')
                            ->image()
                            ->imageEditor()
                            ->maxSize(5120)
                            ->directory('rooms')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'])
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file): string {
                                return \Illuminate\Support\Str::ulid() . '.webp';
                            })
                            ->saveUploadedFileUsing(function (UploadedFile $file, $get) {
                                $imageService = app(ImageService::class);
                                
                                // Delete old image if exists
                                $oldImage = $get('image');
                                if ($oldImage) {
                                    $imageService->deleteImage($oldImage);
                                }
                                
                                // Convert and save as WebP
                                return $imageService->saveAsWebP($file, 'rooms', 85);
                            })
                            ->helperText('Obrázek bude automaticky převeden do formátu WebP a optimalizován'),
                    ])->columns(1),

                Forms\Components\Section::make('Power monitoring')
                    ->schema([
                        Forms\Components\Toggle::make('power_monitoring_enabled')
                            ->label('Aktivovat měření spotřeby')
                            ->reactive()
                            ->helperText('Zapnutí monitorování spotřeby elektřiny'),
                        Forms\Components\Select::make('power_monitoring_type')
                            ->label('Typ měření')
                            ->options([
                                'lights' => 'Pouze světla',
                                'outlets' => 'Pouze zásuvky',
                                'both' => 'Světla i zásuvky',
                            ])
                            ->visible(fn (Forms\Get $get) => $get('power_monitoring_enabled'))
                            ->required(fn (Forms\Get $get) => $get('power_monitoring_enabled')),
                        Forms\Components\Toggle::make('auto_lights_enabled')
                            ->label('Automatické rozsvícení světel')
                            ->helperText('Světla se automaticky rozsvítí při vstupu'),
                        Forms\Components\Toggle::make('auto_outlets_enabled')
                            ->label('Automatické zapnutí zásuvek')
                            ->helperText('Zásuvky se automaticky zapnou při vstupu'),
                    ])->columns(2),

                Forms\Components\Section::make('Řízení přístupu')
                    ->schema([
                        Forms\Components\Select::make('access_control_device')
                            ->label('Zařízení pro řízení vstupu')
                            ->options([
                                'qr_reader' => 'QR čtečka',
                                'keypad' => 'Klávesnice (RFID/PIN)',
                                'both' => 'QR čtečka i klávesnice',
                            ])
                            ->helperText('Vyberte zařízení pro kontrolu přístupu'),
                        Forms\Components\Select::make('access_mode')
                            ->label('Režim přístupu')
                            ->options([
                                'strict' => 'Striktní (přesný čas rezervace)',
                                'lenient' => 'Benevolentní (tolerantní časové okno)',
                            ])
                            ->default('lenient')
                            ->required()
                            ->helperText('Striktní = pouze v čase rezervace, Benevolentní = ±15 minut'),
                    ])->columns(2),

                Forms\Components\Section::make('Další zařízení')
                    ->schema([
                        Forms\Components\Toggle::make('camera_enabled')
                            ->label('Kamera aktivní')
                            ->helperText('Aktivace IP kamery v místnosti'),
                        Forms\Components\Toggle::make('mixer_enabled')
                            ->label('Mixážní pult aktivní')
                            ->helperText('Aktivace digitálního mixážního pultu'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Název')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\ImageColumn::make('image')
                    ->label('Obrázek')
                    ->square()
                    ->defaultImageUrl(url('/images/no-room-image.png')),
                Tables\Columns\TextColumn::make('location')
                    ->label('Umístění')
                    ->searchable(),
                Tables\Columns\TextColumn::make('description')
                    ->label('Popis')
                    ->limit(50)
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('size')
                    ->label('Rozměr')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->label('Adresa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('capacity')
                    ->label('Kapacita')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_hour')
                    ->label('Cena za hodinu')
                    ->money('CZK')
                    ->sortable(),
                Tables\Columns\IconColumn::make('enabled')
                    ->label('Aktivní')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_public')
                    ->label('Veřejná')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('power_monitoring_enabled')
                    ->label('Power monitoring')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('access_mode')
                    ->label('Režim přístupu')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'strict' => 'danger',
                        'lenient' => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'strict' => 'Striktní',
                        'lenient' => 'Benevolentní',
                        default => $state,
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('camera_enabled')
                    ->label('Kamera')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\IconColumn::make('mixer_enabled')
                    ->label('Mixér')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Upraveno')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Aktivní místnosti'),
                Tables\Filters\TernaryFilter::make('is_public')
                    ->label('Veřejné místnosti'),
                Tables\Filters\TernaryFilter::make('power_monitoring_enabled')
                    ->label('S power monitoringem'),
                Tables\Filters\SelectFilter::make('access_mode')
                    ->label('Režim přístupu')
                    ->options([
                        'strict' => 'Striktní',
                        'lenient' => 'Benevolentní',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('toggleEnabled')
                    ->label(fn (Room $record) => $record->enabled ? 'Vypnout' : 'Zapnout')
                    ->icon(fn (Room $record) => $record->enabled ? 'heroicon-o-power' : 'heroicon-m-power')
                    ->color(fn (Room $record) => $record->enabled ? 'warning' : 'success')
                    ->action(function (Room $record): void {
                        $record->update(['enabled' => !$record->enabled]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Místnost '{$record->name}' je nyní " . ($record->enabled ? 'aktivní' : 'vypnutá'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (Room $record) => $record->enabled ? 'Vypnout místnost?' : 'Zapnout místnost?')
                    ->modalDescription(fn (Room $record) => $record->enabled 
                        ? 'Vypnutá místnost bude nedostupná pro nové rezervace'
                        : 'Zapnutá místnost bude dostupná pro nové rezervace')
                    ->modalSubmitActionLabel('Potvrdit')
                    ->modalCancelActionLabel('Zrušit'),
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
            RelationManagers\EquipmentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }
}
