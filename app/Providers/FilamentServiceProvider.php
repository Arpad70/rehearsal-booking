<?php

namespace App\Providers;

use Filament\Panel;
use Filament\PanelProvider;
use App\Filament\Pages\AdminDashboard;
use App\Filament\Resources\RoomReaderResource;
use App\Filament\Resources\GlobalReaderResource;
use App\Filament\Resources\ServiceAccessResource;
use App\Filament\Resources\ReaderAlertResource;
use App\Filament\Resources\PowerMonitoringResource;

class FilamentServiceProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->default()
            ->pages([
                AdminDashboard::class,
            ])
            ->resources([
                RoomReaderResource::class,
                GlobalReaderResource::class,
                ServiceAccessResource::class,
                ReaderAlertResource::class,
                PowerMonitoringResource::class,
            ]);
    }
}  