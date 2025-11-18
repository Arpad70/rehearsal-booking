<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceAccessResource\Pages;
use App\Models\ServiceAccess;
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

class ServiceAccessResource extends Resource
{
    protected static ?string $model = ServiceAccess::class;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    
    protected static ?string $navigationGroup = 'Access Control';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Service Access Configuration')
                    ->description('Grant service staff access to facilities')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->required()
                            ->label('Staff Member'),

                        Forms\Components\Select::make('access_type')
                            ->options([
                                'cleaning' => 'Cleaning / Janitorial',
                                'maintenance' => 'Maintenance / Repairs',
                                'admin' => 'Administration',
                            ])
                            ->required()
                            ->label('Access Type'),

                        Forms\Components\TextInput::make('access_code')
                            ->label('Access Code (for QR)')
                            ->helperText('Unique code embedded in QR - auto-generated if left empty')
                            ->unique(ignoreRecord: true)
                            ->nullable(),

                        Forms\Components\Textarea::make('description')
                            ->label('Reason / Notes')
                            ->placeholder('Why is this access being granted?')
                            ->rows(3)
                            ->nullable(),
                    ]),

                Section::make('Room Access Control')
                    ->schema([
                        Forms\Components\Toggle::make('unlimited_access')
                            ->label('Unlimited Room Access')
                            ->helperText('Allow access to all rooms')
                            ->reactive()
                            ->default(false),

                        Forms\Components\TagsInput::make('allowed_rooms')
                            ->label('Allowed Room IDs')
                            ->helperText('Leave empty or use "*" for all rooms')
                            ->hidden(fn (Forms\Get $get) => $get('unlimited_access'))
                            ->nullable()
                            ->placeholder('e.g., 1, 2, 3'),
                    ]),

                Section::make('Time Restrictions')
                    ->schema([
                        Forms\Components\DateTimePicker::make('valid_from')
                            ->label('Valid From')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('valid_until')
                            ->label('Valid Until')
                            ->nullable(),
                    ]),

                Section::make('Status')
                    ->schema([
                        Forms\Components\Toggle::make('enabled')
                            ->label('Enable Access')
                            ->default(true),

                        Forms\Components\Toggle::make('revoked')
                            ->label('Revoked')
                            ->helperText('Disable this access without deleting')
                            ->default(false),

                        Forms\Components\Textarea::make('revoke_reason')
                            ->label('Revoke Reason')
                            ->hidden(fn (Forms\Get $get) => !$get('revoked'))
                            ->nullable()
                            ->rows(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Staff Member')
                    ->searchable()
                    ->sortable(),

                BadgeColumn::make('access_type')
                    ->label('Type')
                    ->color(fn (string $state): string => match ($state) {
                        'cleaning' => 'blue',
                        'maintenance' => 'orange',
                        'admin' => 'red',
                    }),

                TextColumn::make('access_code')
                    ->label('Code')
                    ->copyable()
                    ->limit(10)
                    ->placeholder('(auto-gen)'),

                TextColumn::make('valid_from')
                    ->label('Valid From')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valid_until')
                    ->label('Valid Until')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\IconColumn::make('enabled')
                    ->label('Status')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('usage_count')
                    ->label('Used')
                    ->alignCenter()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_type')
                    ->options([
                        'cleaning' => 'Cleaning',
                        'maintenance' => 'Maintenance',
                        'admin' => 'Admin',
                    ]),

                Tables\Filters\TernaryFilter::make('enabled')
                    ->label('Status'),

                Tables\Filters\TernaryFilter::make('revoked')
                    ->label('Revoked'),

                Tables\Filters\TernaryFilter::make('unlimited_access')
                    ->label('Unlimited Access'),
            ])
            ->actions([
                Tables\Actions\Action::make('generate_qr')
                    ->label('Generate QR')
                    ->icon('heroicon-m-qr-code')
                    ->color('success')
                    ->action(function (ServiceAccess $record): void {
                        if (!$record->access_code) {
                            $record->update([
                                'access_code' => bin2hex(random_bytes(16)),
                            ]);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('QR Code Ready')
                            ->body("Access code: " . $record->access_code)
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('toggleEnabled')
                    ->label(fn (ServiceAccess $record) => $record->enabled ? 'Vypnout' : 'Zapnout')
                    ->icon(fn (ServiceAccess $record) => $record->enabled ? 'heroicon-o-power' : 'heroicon-m-power')
                    ->color(fn (ServiceAccess $record) => $record->enabled ? 'warning' : 'success')
                    ->action(function (ServiceAccess $record): void {
                        $record->update(['enabled' => !$record->enabled]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title("Přístup uživatele '{$record->user->name}' je nyní " . ($record->enabled ? 'aktivní' : 'vypnutý'))
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading(fn (ServiceAccess $record) => $record->enabled ? 'Vypnout přístup?' : 'Zapnout přístup?')
                    ->modalDescription(fn (ServiceAccess $record) => $record->enabled 
                        ? "Přístup uživatele {$record->user->name} bude dočasně vypnut"
                        : "Přístup uživatele {$record->user->name} bude znovu aktivován")
                    ->modalSubmitActionLabel('Potvrdit')
                    ->modalCancelActionLabel('Zrušit'),

                Tables\Actions\Action::make('revoke')
                    ->label('Revoke')
                    ->icon('heroicon-m-lock-closed')
                    ->color('danger')
                    ->visible(fn (ServiceAccess $record) => !$record->revoked && $record->enabled)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Reason')
                            ->required(),
                    ])
                    ->action(function (ServiceAccess $record, array $data): void {
                        $record->revoke($data['reason']);

                        \Filament\Notifications\Notification::make()
                            ->title('Access Revoked')
                            ->body("Access for {$record->user->name} has been revoked")
                            ->success()
                            ->send();
                    }),

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
            'index' => Pages\ListServiceAccess::route('/'),
            'create' => Pages\CreateServiceAccess::route('/create'),
            'edit' => Pages\EditServiceAccess::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return 'Service Access';
    }

    public static function getPluralLabel(): ?string
    {
        return 'Service Access';
    }
}
