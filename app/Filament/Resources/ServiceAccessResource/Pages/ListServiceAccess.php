<?php

namespace App\Filament\Resources\ServiceAccessResource\Pages;

use App\Filament\Resources\ServiceAccessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceAccess extends ListRecords
{
    protected static string $resource = ServiceAccessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
