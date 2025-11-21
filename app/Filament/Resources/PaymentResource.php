<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Models\Payment;
use Filament\Forms\Form as FormsForm;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables\Table as TablesTable;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    // Use an existing heroicon name to avoid SvgNotFound (currency icon exists)
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';

    public static function form(FormsForm $form): FormsForm
    {
        return $form->schema([
            Select::make('reservation_id')
                ->label('Rezervace')
                ->relationship('reservation', 'id')
                ->searchable()
                ->preload()
                ->nullable(),

            TextInput::make('amount')
                ->label('Částka')
                ->required()
                ->numeric()
                ->minValue(0),

            TextInput::make('currency')
                ->label('Měna')
                ->default('CZK')
                ->required(),

            DateTimePicker::make('paid_at')
                ->label('Datum platby')
                ->nullable(),
        ]);
    }

    public static function table(TablesTable $table): TablesTable
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('reservation.id')->label('Rezervace')->sortable(),
                TextColumn::make('amount')->label('Částka')->money('CZK'),
                TextColumn::make('currency')->label('Měna'),
                TextColumn::make('paid_at')->label('Zaplatil')->dateTime(),
                TextColumn::make('created_at')->label('Vytvořeno')->dateTime(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
