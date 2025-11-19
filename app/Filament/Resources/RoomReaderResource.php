<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomReaderResource\Pages;
use App\Models\RoomReader;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;

class RoomReaderResource extends Resource
{
    protected static ?string $model = RoomReader::class;

    protected static ?string $navigationIcon = 'heroicon-o-wifi';
    
    protected static ?string $navigationGroup = 'Device Management';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Reader Configuration')
                    ->description('Configure the QR reader for this room')
                    ->schema([
                        Forms\Components\Select::make('room_id')
                            ->relationship('room', 'name')
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('reader_name')
                            ->label('Reader Name')
                            ->placeholder('e.g., "QR Reader - Room 1"')
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
                            ->required()
                            ->placeholder('Token for authentication'),

                        Forms\Components\Toggle::make('enabled')
                            ->label('Enable Reader')
                            ->default(true),
                    ]),

                Section::make('Door Lock Configuration')
                    ->description('Configure how the door lock is triggered')
                    ->schema([
                        Forms\Components\Select::make('door_lock_type')
                            ->label('Lock Type')
                            ->options([
                                'relay' => 'Relay (GPIO/Arduino/Shelly)',
                                'api' => 'Smart Lock API',
                                'webhook' => 'Webhook',
                            ])
                            ->default('relay')
                            ->required()
                            ->reactive(),

                        Forms\Components\KeyValue::make('door_lock_config')
                            ->label('Lock Configuration (JSON)')
                            ->addButtonLabel('Add Configuration')
                            ->keyPlaceholder('Key (e.g., relay_pin, duration)')
                            ->valuePlaceholder('Value'),
                    ]),

                Section::make('Configuration Details')
                    ->collapsed()
                    ->schema([
                        Forms\Components\Placeholder::make('config_info')
                            ->label('Configuration Information')
                            ->content('Configure the lock type above to set up access control. Relay configurations require GPIO pin numbers and duration settings.'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('reader_name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('room.name')
                    ->label('Room')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('reader_ip')
                    ->label('IP Address')
                    ->copyable(),

                TextColumn::make('door_lock_type')
                    ->label('Lock Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'relay' => 'gray',
                        'api' => 'info',
                        'webhook' => 'warning',
                    }),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Status'),
            ])
            ->actions([
                Tables\Actions\Action::make('test')
                    ->label('Test Connection')
                    ->icon('heroicon-m-play')
                    ->action(function (RoomReader $record): void {
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
                    ->label(fn (RoomReader $record) => $record->enabled ? 'Vypnout' : 'Zapnout')
                    ->icon(fn (RoomReader $record) => $record->enabled ? 'heroicon-o-power' : 'heroicon-m-power')
                    ->color(fn (RoomReader $record) => $record->enabled ? 'warning' : 'success')
                    ->action(function (RoomReader $record): void {
                        $record->update(['enabled' => !$record->enabled]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Čtečka '{$record->reader_name}' je nyní " . ($record->enabled ? 'aktivní' : 'vypnutá'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (RoomReader $record) => $record->enabled ? 'Vypnout čtečku?' : 'Zapnout čtečku?')
                    ->modalDescription(fn (RoomReader $record) => $record->enabled 
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
            'index' => Pages\ListRoomReaders::route('/'),
            'create' => Pages\CreateRoomReader::route('/create'),
            'edit' => Pages\EditRoomReader::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Room Reader';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Room Readers';
    }
}
