<?php

namespace App\Filament\Resources\PowerMonitoringResource\Pages;

use App\Filament\Resources\PowerMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListPowerMonitorings extends ListRecords
{
    protected static string $resource = PowerMonitoringResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('collect')
                ->label('Collect Data Now')
                ->icon('heroicon-m-arrow-path')
                ->color('success')
                ->action(function () {
                    dispatch(new \App\Jobs\CollectPowerMonitoringDataJob());
                    
                    Notification::make()
                        ->success()
                        ->title('Power data collection started')
                        ->body('Power data collection started in background')
                        ->send();
                }),
        ];
    }
}
