<?php

namespace App\Filament\Resources\GlobalReaderResource\Pages;

use App\Filament\Resources\GlobalReaderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGlobalReader extends EditRecord
{
    protected static string $resource = GlobalReaderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
