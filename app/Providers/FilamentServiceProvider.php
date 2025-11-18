<?php

namespace App\Providers;

use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Navigation\NavigationItem;
use App\Filament\Pages\AdminDashboard;
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