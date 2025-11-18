<?php  
use Illuminate\Support\Facades\Route;  
use App\Http\Controllers\Api\RoomController;  
use App\Http\Controllers\Api\AccessController;  
use App\Http\Middleware\ThrottleAccessValidation;

// Rate limit all API routes: 60 requests per minute
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/rooms', [RoomController::class,'index']);  
    Route::get('/rooms/{room}/availability', [RoomController::class,'availability']);  
    
    // Access validation has stricter rate limiting via custom middleware
    Route::post('/access/validate', [AccessController::class,'validateAccess'])
        ->middleware(ThrottleAccessValidation::class)
        ->withoutMiddleware('throttle:60,1');
    
    Route::post('/reservations', [\App\Http\Controllers\ReservationController::class,'store'])->middleware('auth:sanctum');  
});
