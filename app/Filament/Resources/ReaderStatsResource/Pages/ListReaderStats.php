<?php

namespace App\Filament\Resources\ReaderStatsResource\Pages;

use App\Filament\Resources\ReaderStatsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReaderStats extends ListRecords
{
    protected static string $resource = ReaderStatsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Žádné akce pro view-only report
        ];
    }
}
