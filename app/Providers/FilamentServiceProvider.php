<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\AdminDashboard;
use App\Filament\Resources\RoomReaderResource;
use App\Filament\Resources\GlobalReaderResource;
use App\Filament\Resources\ServiceAccessResource;
use App\Filament\Resources\ReaderAlertResource;
use App\Filament\Resources\PowerMonitoringResource;
use App\Filament\Widgets\PowerMonitoringStats;
use App\Filament\Widgets\PowerConsumptionChart;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Filament::registerPanels([
            Panel::make()
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
                ])
                ->navigation(function (): array {
                    return [
                        NavigationItem::make('Admin Panel')
                            ->icon('heroicon-o-home')
                            ->isActiveWhen(fn (): bool => request()->routeIs('filament.admin.pages.admin-dashboard'))
                            ->url(AdminDashboard::getUrl()),
                    ];
                })
        ]);
    }
}  