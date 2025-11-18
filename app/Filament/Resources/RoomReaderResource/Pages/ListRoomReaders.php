<?php

namespace App\Filament\Resources\RoomReaderResource\Pages;

use App\Filament\Resources\RoomReaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoomReaders extends ListRecords
{
    protected static string $resource = RoomReaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
