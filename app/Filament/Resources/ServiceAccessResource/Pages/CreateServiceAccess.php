<?php

namespace App\Filament\Resources\ServiceAccessResource\Pages;

use App\Filament\Resources\ServiceAccessResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceAccess extends CreateRecord
{
    protected static string $resource = ServiceAccessResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate access code if not provided
        if (empty($data['access_code'])) {
            $data['access_code'] = bin2hex(random_bytes(16));
        }

        return $data;
    }
}
