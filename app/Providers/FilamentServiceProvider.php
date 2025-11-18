<?php  

namespace App\Providers;  

use Filament\Facades\Filament;  
use Filament\Panel;  
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
        ]);  
    }  
}  