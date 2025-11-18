<?php

namespace App\Filament\Resources\AccessReportResource\Pages;

use App\Filament\Resources\AccessReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAccessReports extends ListRecords
{
    protected static string $resource = AccessReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->hidden(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            AccessReportResource\Widgets\AccessReportStats::class,
        ];
    }
}
