<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessLogResource\Pages;
use App\Filament\Resources\AccessLogResource\RelationManagers;
use App\Models\AccessLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccessLogResource extends Resource
{
    protected static ?string $model = AccessLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    
    protected static ?string $navigationGroup = 'Správa vybavení';
    
    protected static ?string $navigationLabel = 'Historie přístupů';
    
    protected static ?int $navigationSort = 10;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('reservation_id')
                    ->numeric(),
                Forms\Components\Select::make('room_id')
                    ->relationship('room', 'name'),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name'),
                Forms\Components\TextInput::make('location')
                    ->maxLength(255),
                Forms\Components\TextInput::make('action')
                    ->maxLength(255),
                Forms\Components\TextInput::make('result')
                    ->maxLength(255),
                Forms\Components\Toggle::make('access_granted')
                    ->required(),
                Forms\Components\TextInput::make('failure_reason')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ip')
                    ->maxLength(255),
                Forms\Components\TextInput::make('access_code')
                    ->maxLength(255),
                Forms\Components\TextInput::make('access_type'),
                Forms\Components\TextInput::make('access_method'),
                Forms\Components\TextInput::make('device_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('scan_id')
                    ->maxLength(255),
                Forms\Components\TextInput::make('reader_type')
                    ->required(),
                Forms\Components\Select::make('global_reader_id')
                    ->relationship('globalReader', 'id'),
                Forms\Components\TextInput::make('ip_address')
                    ->maxLength(255),
                Forms\Components\TextInput::make('user_agent')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('validated_at'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Datum a čas')
                    ->dateTime('d.m.Y H:i:s')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uživatel')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('room.name')
                    ->label('Místnost')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('action')
                    ->label('Akce')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('result')
                    ->label('Výsledek')
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('access_granted')
                    ->label('Přístup povolen')
                    ->boolean()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('location')
                    ->label('Umístění')
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP adresa')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Uživatel')
                    ->relationship('user', 'name')
                    ->preload(),
                    
                Tables\Filters\SelectFilter::make('room_id')
                    ->label('Místnost')
                    ->relationship('room', 'name')
                    ->preload(),
                    
                Tables\Filters\TernaryFilter::make('access_granted')
                    ->label('Přístup povolen')
                    ->placeholder('Všechny')
                    ->trueLabel('Ano')
                    ->falseLabel('Ne'),
                    
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('date_from')
                            ->label('Od'),
                        Forms\Components\DatePicker::make('date_to')
                            ->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['date_to'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
                        
                        Forms\Components\Select::make('user_id')
                            ->label('Uživatel')
                            ->options(fn() => \App\Models\User::pluck('name', 'id')->toArray())
                            ->placeholder('Všichni')
                            ->searchable(),
                        
                        Forms\Components\Select::make('room_id')
                            ->label('Místnost')
                            ->options(fn() => \App\Models\Room::pluck('name', 'id')->toArray())
                            ->placeholder('Všechny')
                            ->searchable(),
                        
                        Forms\Components\Toggle::make('access_granted')
                            ->label('Pouze s povoleným přístupem'),
                    ])
                    ->action(function (array $data) {
                        return response()->download(
                            (new \App\Exports\AccessLogsExport($data))->store('access_logs_export.xlsx', 'local', \Maatwebsite\Excel\Excel::XLSX),
                            'pristupy_' . now()->format('Y-m-d_His') . '.xlsx'
                        );
                    }),
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
            'index' => Pages\ListAccessLogs::route('/'),
        ];
    }
    
    public static function canCreate(): bool
    {
        return false;
    }
}
