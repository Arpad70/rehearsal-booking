<?php

namespace App\Filament\Resources\DeviceResource\Pages;

use App\Filament\Resources\DeviceResource;
use App\Filament\Widgets\DeviceStatusOverview;
use App\Services\DeviceHealthService;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListDevices extends ListRecords
{
    protected static string $resource = DeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('checkAll')
                ->label('Otestovat vše')
                ->icon('heroicon-o-arrow-path')
                ->color('info')
                ->action(function () {
                    $healthService = app(DeviceHealthService::class);
                    $results = $healthService->checkAllDevices();
                    
                    $online = count(array_filter($results, fn($r) => $r['status'] === 'online'));
                    $offline = count($results) - $online;
                    
                    Notification::make()
                        ->success()
                        ->title('Health check dokončen')
                        ->body("Online: {$online}, Offline: {$offline}")
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Přidat zařízení')
                ->icon('heroicon-o-plus'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            DeviceStatusOverview::class,
        ];
    }
}
