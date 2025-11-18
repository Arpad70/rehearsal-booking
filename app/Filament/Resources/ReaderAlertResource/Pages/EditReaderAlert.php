<?php

namespace App\Filament\Resources\ReaderAlertResource\Pages;

use App\Filament\Resources\ReaderAlertResource;
use Filament\Resources\Pages\EditRecord;

class EditReaderAlert extends EditRecord
{
    protected static string $resource = ReaderAlertResource::class;

    public function getTitle(): string
    {
        return '⚠️ Úprava upozornění';
    }
}
