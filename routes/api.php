<?php  
use Illuminate\Support\Facades\Route;

// V1 API endpoints
Route::prefix('v1')->group(base_path('routes/api/v1.php'));

// Backward compatibility: also available without /v1 prefix
Route::group([], base_path('routes/api/v1.php'));

// Device Webhooks (from simulators) - Public, throttled
Route::prefix('webhooks')->name('webhooks.')->middleware('throttle:webhooks')->group(function () {
    Route::post('/qr-scan', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'handleQRScan'])
        ->name('qr-scan');
    
    Route::post('/rfid-scan', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'handleRFIDScan'])
        ->name('rfid-scan');
    
    Route::post('/pin-entry', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'handlePINEntry'])
        ->name('pin-entry');
    
    Route::post('/motion-detected', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'handleMotionDetection'])
        ->name('motion-detected');
    
    Route::post('/power-update', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'handlePowerUpdate'])
        ->name('power-update');
    
    Route::post('/mixer-scene-changed', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'handleMixerSceneChange'])
        ->name('mixer-scene-changed');
    
    Route::get('/health', [\App\Http\Controllers\Api\DeviceWebhookController::class, 'healthCheck'])
        ->name('health');
});

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

// Power Monitoring endpoints (authenticated)
Route::prefix('v1/power-monitoring')->name('power-monitoring.')->middleware('auth:sanctum')->group(function () {
    // Collect data
    Route::post('/collect', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'collectAll'])
        ->name('collect-all');
    Route::post('/collect/{deviceId}', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'collectDevice'])
        ->name('collect-device');
    
    // Get data
    Route::get('/{deviceId}', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getDeviceData'])
        ->name('device-data');
    Route::get('/{deviceId}/latest', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getLatest'])
        ->name('latest');
    Route::get('/{deviceId}/channel/{channel}', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getChannelData'])
        ->name('channel-data');
    
    // Statistics
    Route::get('/{deviceId}/stats/energy', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getEnergyStats'])
        ->name('stats-energy');
    Route::get('/{deviceId}/stats/temperature', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getTemperatureStats'])
        ->name('stats-temperature');
    Route::get('/{deviceId}/daily', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getDailyEnergy'])
        ->name('daily-energy');
    
    // Alerts
    Route::get('/{deviceId}/alerts', [\App\Http\Controllers\Api\PowerMonitoringController::class, 'getAlerts'])
        ->name('alerts');
});

// RFID Management endpoints
Route::prefix('v1/rfid')->name('rfid.')->group(function () {
    // Status endpoint (kontrola, zda API běží)
    Route::get('/reader-status', [\App\Http\Controllers\Api\RfidController::class, 'readerStatus'])
        ->name('reader-status');
    
    // Veřejné endpointy (pro USB čtečky)
    Route::post('/read', [\App\Http\Controllers\Api\RfidController::class, 'read'])
        ->name('read')
        ->middleware('throttle:60,1'); // 60 requestů za minutu
    
    Route::post('/check-availability', [\App\Http\Controllers\Api\RfidController::class, 'checkAvailability'])
        ->name('check-availability')
        ->middleware('throttle:60,1');
    
    Route::post('/batch-scan', [\App\Http\Controllers\Api\RfidController::class, 'batchScan'])
        ->name('batch-scan')
        ->middleware('throttle:30,1'); // Nižší limit pro batch operace
    
    // Chráněné endpointy (vyžadují autentizaci)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/write', [\App\Http\Controllers\Api\RfidController::class, 'write'])
            ->name('write');
        
        Route::post('/checkout', [\App\Http\Controllers\Api\RfidController::class, 'checkOut'])
            ->name('checkout');
        
        Route::post('/checkin', [\App\Http\Controllers\Api\RfidController::class, 'checkIn'])
            ->name('checkin');
    });
});
  
Route::post('/reservations', [\App\Http\Controllers\ReservationController::class,'store'])->middleware('auth:sanctum');  