<?php

namespace App\Filament\Resources;

use App\Filament\Resources\GlobalReaderResource\Pages;
use App\Models\GlobalReader;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class GlobalReaderResource extends Resource
{
    protected static ?string $model = GlobalReader::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    
    protected static ?string $navigationGroup = 'Device Management';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Global Reader Configuration')
                    ->description('Configure global access points (entrance, service, admin)')
                    ->schema([
                        Forms\Components\TextInput::make('reader_name')
                            ->label('Reader Name')
                            ->placeholder('e.g., "Main Entrance", "Service Access"')
                            ->required()
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('access_type')
                            ->label('Access Type')
                            ->options([
                                'entrance' => 'Main Entrance',
                                'service' => 'Service / Staff',
                                'admin' => 'Administration',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('reader_ip')
                            ->label('Reader IP Address')
                            ->ipv4()
                            ->required()
                            ->placeholder('192.168.1.100'),

                        Forms\Components\TextInput::make('reader_port')
                            ->label('Reader Port')
                            ->numeric()
                            ->default(8080)
                            ->required(),

                        Forms\Components\TextInput::make('reader_token')
                            ->label('Reader Token (Bearer Token)')
                            ->password()
                            ->required(),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Enable Reader')
                            ->default(true),
                    ]),

                Section::make('Access Window Configuration')
                    ->description('Define how far before and after reservation time access is allowed')
                    ->schema([
                        Forms\Components\TextInput::make('access_minutes_before')
                            ->label('Minutes Before Reservation Start')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->helperText('User can access this many minutes before their reservation starts'),

                        Forms\Components\TextInput::make('access_minutes_after')
                            ->label('Minutes After Reservation End')
                            ->numeric()
                            ->default(30)
                            ->required()
                            ->helperText('User can access this many minutes after their reservation ends'),
                    ]),

                Section::make('Door Lock Configuration')
                    ->schema([
                        Forms\Components\Select::make('door_lock_type')
                            ->label('Lock Type')
                            ->options([
                                'relay' => 'Relay (GPIO/Arduino/Shelly)',
                                'api' => 'Smart Lock API',
                                'webhook' => 'Webhook',
                            ])
                            ->default('relay')
                            ->required(),

                        Forms\Components\KeyValue::make('door_lock_config')
                            ->label('Lock Configuration')
                            ->addButtonLabel('Add Configuration')
                            ->keyPlaceholder('Key')
                            ->valuePlaceholder('Value')
                            ->reorderable(false)
                            ->disableAllToolbarButtons(),
                    ]),

                Section::make('Service Access Control')
                    ->collapsed()
                    ->schema([
                        Forms\Components\CheckboxList::make('allowed_service_types')
                            ->label('Allowed Service Access Types')
                            ->options([
                                'cleaning' => 'Cleaning / Janitorial',
                                'maintenance' => 'Maintenance',
                                'admin' => 'Administration',
                            ])
                            ->helperText('Leave empty to allow all service types'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reader_name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                BadgeColumn::make('access_type')
                    ->label('Type')
                    ->color(fn (string $state): string => match ($state) {
                        'entrance' => 'success',
                        'service' => 'warning',
                        'admin' => 'danger',
                    }),

                TextColumn::make('reader_ip')
                    ->label('IP Address')
                    ->copyable(),

                TextColumn::make('access_minutes_before')
                    ->label('Before (min)')
                    ->alignCenter(),

                TextColumn::make('access_minutes_after')
                    ->label('After (min)')
                    ->alignCenter(),

                BadgeColumn::make('enabled')
                    ->label('Status')
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ]),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_type')
                    ->options([
                        'entrance' => 'Main Entrance',
                        'service' => 'Service / Staff',
                        'admin' => 'Administration',
                    ]),

                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Test')
                    ->icon('heroicon-m-play')
                    ->action(function (GlobalReader $record): void {
                        $result = $record->testConnection();
                        $message = $result['success']
                            ? "✅ " . $result['message']
                            : "❌ " . $result['message'];
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Connection Test')
                            ->body($message)
                            ->success($result['success'])
                            ->send();
                    }),

                Tables\Actions\Action::make('toggleEnabled')
                    ->label(fn (GlobalReader $record) => $record->enabled ? 'Vypnout' : 'Zapnout')
                    ->icon(fn (GlobalReader $record) => $record->enabled ? 'heroicon-o-power' : 'heroicon-m-power')
                    ->color(fn (GlobalReader $record) => $record->enabled ? 'warning' : 'success')
                    ->action(function (GlobalReader $record): void {
                        $record->update(['enabled' => !$record->enabled]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Čtečka '{$record->reader_name}' je nyní " . ($record->enabled ? 'aktivní' : 'vypnutá'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (GlobalReader $record) => $record->enabled ? 'Vypnout čtečku?' : 'Zapnout čtečku?')
                    ->modalDescription(fn (GlobalReader $record) => $record->enabled 
                        ? 'Vypnutá čtečka nebude přijímat QR kódy'
                        : 'Zapnutá čtečka bude přijímat QR kódy')
                    ->modalSubmitActionLabel('Potvrdit')
                    ->modalCancelActionLabel('Zrušit'),

                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListGlobalReaders::route('/'),
            'create' => Pages\CreateGlobalReader::route('/create'),
            'edit' => Pages\EditGlobalReader::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Global Reader';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Global Readers';
    }
}
