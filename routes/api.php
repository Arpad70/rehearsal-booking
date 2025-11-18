<?php  
use Illuminate\Support\Facades\Route;

// V1 API endpoints
Route::prefix('v1')->group(base_path('routes/api/v1.php'));

// Backward compatibility: also available without /v1 prefix
Route::group([], base_path('routes/api/v1.php'));
  
Route::post('/reservations', [\App\Http\Controllers\ReservationController::class,'store'])->middleware('auth:sanctum');  