<?php

namespace App\Filament\Resources\RfidManagementResource\Pages;

use App\Filament\Resources\RfidManagementResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;

class ListRfidManagement extends ListRecords
{
    protected static string $resource = RfidManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('inventory')
                ->label('Automatická inventura')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('success')
                ->url(fn (): string => static::$resource::getUrl('inventory')),
            
            Actions\Action::make('reader_setup')
                ->label('Nastavení čtečky')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('gray')
                ->url(fn (): string => static::$resource::getUrl('reader')),
            
            Actions\CreateAction::make()
                ->label('Přidat RFID tag'),
        ];
    }
}
