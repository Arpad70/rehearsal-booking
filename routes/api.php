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

// Device control endpoints (authenticated)
Route::prefix('v1/devices')->name('devices.')->middleware('auth:sanctum')->group(function () {
    // Room (Shelly) controls
    Route::post('/rooms/{room}/toggle', [\App\Http\Controllers\Api\DeviceControlController::class, 'toggleRoom'])
        ->name('rooms.toggle');
    Route::post('/rooms/{room}/enable', [\App\Http\Controllers\Api\DeviceControlController::class, 'enableRoom'])
        ->name('rooms.enable');
    Route::post('/rooms/{room}/disable', [\App\Http\Controllers\Api\DeviceControlController::class, 'disableRoom'])
        ->name('rooms.disable');
    
    // Room Reader (QR reader) controls
    Route::post('/room-readers/{roomReader}/toggle', [\App\Http\Controllers\Api\DeviceControlController::class, 'toggleRoomReader'])
        ->name('room-readers.toggle');
    Route::post('/room-readers/{roomReader}/enable', [\App\Http\Controllers\Api\DeviceControlController::class, 'enableRoomReader'])
        ->name('room-readers.enable');
    Route::post('/room-readers/{roomReader}/disable', [\App\Http\Controllers\Api\DeviceControlController::class, 'disableRoomReader'])
        ->name('room-readers.disable');
    
    // Global Reader controls
    Route::post('/global-readers/{globalReader}/toggle', [\App\Http\Controllers\Api\DeviceControlController::class, 'toggleGlobalReader'])
        ->name('global-readers.toggle');
    
    // Service Access controls
    Route::post('/service-access/{serviceAccess}/toggle', [\App\Http\Controllers\Api\DeviceControlController::class, 'toggleServiceAccess'])
        ->name('service-access.toggle');
});
  
Route::post('/reservations', [\App\Http\Controllers\ReservationController::class,'store'])->middleware('auth:sanctum');  