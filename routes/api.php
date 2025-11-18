<?php  
use Illuminate\Support\Facades\Route;

// V1 API endpoints
Route::prefix('v1')->group(base_path('routes/api/v1.php'));

// Backward compatibility: also available without /v1 prefix
Route::group([], base_path('routes/api/v1.php'));

// QR Access endpoints (public, rate limited)
Route::prefix('v1/qr')->name('qr.')->group(function () {
    Route::post('/validate', [\App\Http\Controllers\Api\QRAccessController::class, 'validateQRAccess'])
        ->name('validate')
        ->middleware('throttle:qr-reader');
    
    Route::get('/status', [\App\Http\Controllers\Api\QRAccessController::class, 'status'])
        ->name('status');
    
    Route::get('/heartbeat', [\App\Http\Controllers\Api\QRAccessController::class, 'heartbeat'])
        ->name('heartbeat');
});

// Room reader management
Route::prefix('v1/rooms')->name('rooms.')->middleware('auth:sanctum')->group(function () {
    Route::post('{roomId}/readers/{readerId}/test', [\App\Http\Controllers\Api\QRAccessController::class, 'testReaderConnection'])
        ->name('readers.test');
});
  
Route::post('/reservations', [\App\Http\Controllers\ReservationController::class,'store'])->middleware('auth:sanctum');  