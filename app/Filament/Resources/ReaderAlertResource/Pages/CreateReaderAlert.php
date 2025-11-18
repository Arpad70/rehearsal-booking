<?php

namespace App\Filament\Resources\ReaderAlertResource\Pages;

use App\Filament\Resources\ReaderAlertResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReaderAlert extends CreateRecord
{
    protected static string $resource = ReaderAlertResource::class;

    public function getTitle(): string
    {
        return 'Nové upozornění';
    }
}
