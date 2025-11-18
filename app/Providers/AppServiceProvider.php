<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;  
use Illuminate\Support\Facades\Vite; 

class AppServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [  
        \App\Models\Room::class => \App\Policies\RoomPolicy::class,  
        \App\Models\Reservation::class => \App\Policies\ReservationPolicy::class,  
    ];  
    
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    Vite::macro('image', fn(string $asset) => Vite::asset("resources/images/{$asset}"));  
    }
}