<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BackupQRCodeResource\Pages;
use App\Models\BackupQRCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BackupQRCodeResource extends Resource
{
    protected static ?string $model = BackupQRCode::class;

    protected static ?string $slug = 'backup-qr-codes';

    protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';

    protected static ?string $navigationGroup = 'QR Reader';

    protected static ?string $navigationLabel = 'Backup QR kódy';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reservation.user.name')
                    ->label('Rezervace')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sequence_number')
                    ->label('Pořadí')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'success' => 'active',
                        'warning' => 'used',
                        'danger' => 'revoked',
                        'gray' => 'expired',
                    ])
                    ->formatStateUsing(fn($state) => match($state) {
                        'active' => 'Aktivní',
                        'used' => 'Použit',
                        'revoked' => 'Zrušen',
                        'expired' => 'Vypršel',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('used_at')
                    ->label('Použit v')
                    ->dateTime('d.m.Y H:i')
                    ->placeholder('Nepoužito')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('used_by_reader')
                    ->label('Čtečka')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Vytvořen')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktivní',
                        'used' => 'Použit',
                        'revoked' => 'Zrušen',
                        'expired' => 'Vypršel',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('revoke')
                    ->label('Zrušit')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn(BackupQRCode $record) => $record->status === 'active')
                    ->action(fn(BackupQRCode $record) => $record->revoke()),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBackupQRCodes::route('/'),
        ];
    }
}
