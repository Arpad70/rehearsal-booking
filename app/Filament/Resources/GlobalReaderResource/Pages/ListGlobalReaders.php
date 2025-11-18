<?php

namespace App\Filament\Resources\GlobalReaderResource\Pages;

use App\Filament\Resources\GlobalReaderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGlobalReaders extends ListRecords
{
    protected static string $resource = GlobalReaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
