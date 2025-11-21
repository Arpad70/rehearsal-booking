<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReservationResource\Pages;
use App\Models\Reservation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReservationResource extends Resource
{
    protected static ?string $model = Reservation::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar';

    protected static ?string $navigationLabel = 'Reservations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('room_id')
                    ->relationship('room', 'name')
                    ->required(),
                Forms\Components\TextInput::make('start_at')
                    ->type('datetime-local')
                    ->required(),
                Forms\Components\TextInput::make('end_at')
                    ->type('datetime-local')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->default('pending'),
                Forms\Components\TextInput::make('access_token')
                    ->disabled()
                    ->dehydrated(false),
                Forms\Components\TextInput::make('token_valid_from')
                    ->type('datetime-local'),
                Forms\Components\TextInput::make('token_expires_at')
                    ->type('datetime-local'),
                Forms\Components\TextInput::make('used_at')
                    ->label('Used at')
                    ->type('datetime-local'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('room.name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('start_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('end_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'pending' => 'gray',
                        'active' => 'blue',
                        'completed' => 'green',
                        'cancelled' => 'red',
                    ]),
                Tables\Columns\TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('room_id')
                    ->relationship('room', 'name'),
                Tables\Filters\SelectFilter::make('user_id')
                    ->relationship('user', 'name'),
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
                Tables\Actions\Action::make('export')
                    ->label('Exportovat do Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Od data')
                            ->default(now()->subMonth()),
                        
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Do data')
                            ->default(now()),
                        
                        Forms\Components\Select::make('room_id')
                            ->label('Místnost')
                            ->relationship('room', 'name')
                            ->placeholder('Všechny')
                            ->preload(),
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Uživatel')
                            ->relationship('user', 'name')
                            ->placeholder('Všichni')
                            ->preload(),
                        
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Čekající',
                                'active' => 'Aktivní',
                                'completed' => 'Dokončeno',
                                'cancelled' => 'Zrušeno',
                            ])
                            ->placeholder('Všechny'),
                    ])
                    ->action(function (array $data) {
                        return response()->download(
                            (new \App\Exports\ReservationsExport($data))->store('reservations_export.xlsx', 'local', \Maatwebsite\Excel\Excel::XLSX),
                            'rezervace_' . now()->format('Y-m-d_His') . '.xlsx'
                        );
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReservations::route('/'),
            'create' => Pages\CreateReservation::route('/create'),
            'edit' => Pages\EditReservation::route('/{record}/edit'),
        ];
    }
}
