<?php

namespace App\Filament\Resources\PowerMonitoringResource\Pages;

use App\Filament\Resources\PowerMonitoringResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

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
                    $this->notify(
                        'success',
                        'Power data collection started in background'
                    );
                }),
        ];
    }
}
