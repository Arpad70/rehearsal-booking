<?php

namespace App\Filament\Resources\RfidManagementResource\Pages;

use App\Filament\Resources\RfidManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRfidManagement extends EditRecord
{
    protected static string $resource = RfidManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Odebrat tag')
                ->modalHeading('Odebrat identifikační tag')
                ->action(function () {
                    $this->record->update([
                        'tag_id' => null,
                        'tag_type' => null,
                    ]);
                    return redirect($this->getResource()::getUrl('index'));
                }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
