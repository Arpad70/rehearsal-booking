<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessReportResource\Pages;
use App\Models\AccessLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AccessReportResource extends Resource
{
    protected static ?string $model = AccessLog::class;

    protected static ?string $slug = 'access-reports';

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationGroup = 'Reporty';

    protected static ?string $navigationLabel = 'Přístupové reporty';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Detaily přístupu')
                    ->schema([
                        Forms\Components\TextInput::make('user_id')
                            ->label('Uživatel')
                            ->disabled(),

                        Forms\Components\Select::make('access_type')
                            ->label('Typ přístupu')
                            ->options([
                                'reservation' => 'Rezervace',
                                'service' => 'Servisní kód',
                            ])
                            ->disabled(),

                        Forms\Components\Select::make('reader_type')
                            ->label('Typ čtečky')
                            ->options([
                                'room' => 'Místnost',
                                'global' => 'Globální',
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('access_code')
                            ->label('Přístupový kód')
                            ->disabled(),

                        Forms\Components\Toggle::make('access_granted')
                            ->label('Přístup povolen')
                            ->disabled(),

                        Forms\Components\TextInput::make('failure_reason')
                            ->label('Důvod chyby')
                            ->disabled(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Technické detaily')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP adresa')
                            ->disabled(),

                        Forms\Components\TextInput::make('user_agent')
                            ->label('User agent')
                            ->disabled()
                            ->columnSpanFull(),

                        Forms\Components\DateTimePicker::make('validated_at')
                            ->label('Čas ověření')
                            ->disabled(),

                        Forms\Components\DateTimePicker::make('created_at')
                            ->label('Čas záznamu')
                            ->disabled(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Čas')
                    ->dateTime('H:i:s')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Uživatel')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('access_type')
                    ->label('Typ')
                    ->badge()
                    ->colors([
                        'blue' => 'reservation',
                        'gray' => 'service',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'reservation' => 'Rezervace',
                        'service' => 'Servis',
                        default => $state,
                    })
                    ->toggleable(),

                Tables\Columns\TextColumn::make('access_granted')
                    ->label('Výsledek')
                    ->badge()
                    ->colors([
                        'success' => true,
                        'danger' => false,
                    ])
                    ->formatStateUsing(fn($state) => $state ? 'Přístup povolen' : 'Přístup odepřen')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('reader_type')
                    ->label('Čtečka')
                    ->badge()
                    ->colors([
                        'blue' => 'room',
                        'gray' => 'global',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'room' => 'Místnost',
                        'global' => 'Globální',
                        default => $state,
                    })
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('access_granted')
                    ->label('Výsledek')
                    ->options([
                        '1' => 'Úspěšně',
                        '0' => 'Selhalo',
                    ]),

                Tables\Filters\SelectFilter::make('access_type')
                    ->label('Typ přístupu')
                    ->options([
                        'reservation' => 'Rezervace',
                        'service' => 'Servis',
                    ]),

                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Od'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Do'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50, 100])
            ->persistSearchInSession()
            ->persistFiltersInSession();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAccessReports::route('/'),
        ];
    }
}
