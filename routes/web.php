<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;  
use App\Http\Controllers\DeviceController;  
use Illuminate\Support\Facades\Auth;



Route::get('/', function () {
    $rooms = \App\Models\Room::with('devices')->where('is_public', true)->get();
    return view('landing', compact('rooms'));
})->name('landing');

// (Route /nastenka was removed — nástěnka controller/page deleted)

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = Auth::user();
    $reservations = $user->reservations()
        ->with('room')
        ->where('end_at', '>', now())
        ->orderBy('start_at')
        ->get();
    
    // Získání dostupných místností
    $rooms = \App\Models\Room::where('enabled', true)
        ->where('is_public', true)
        ->orderBy('name')
        ->get();
    
    return view('dashboard', compact('reservations', 'rooms'));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->group(function () {
    Route::resource('reservations', ReservationController::class)->only(['index','create','store','show','edit','update','destroy']);
    Route::get('reservations/{reservation}/qr', [ReservationController::class, 'showQr'])->name('reservations.qr');
    Route::resource('rooms', RoomController::class)->only(['index','show']);
    
    // Payment routes
    Route::post('reservations/{reservation}/payment', [\App\Http\Controllers\PaymentController::class, 'create'])->name('payment.create');
    Route::post('payments/{payment}/refund', [\App\Http\Controllers\PaymentController::class, 'refund'])->name('payment.refund');
    Route::get('payments/{payment}/status', [\App\Http\Controllers\PaymentController::class, 'status'])->name('payment.status');
});

// Public payment callback routes (no auth required)
Route::get('payment/{payment}/callback', [\App\Http\Controllers\PaymentController::class, 'callback'])->name('payment.callback');
Route::get('payment/{payment}/cancel', [\App\Http\Controllers\PaymentController::class, 'cancel'])->name('payment.cancel');
Route::post('payment/webhook/{gateway}', [\App\Http\Controllers\PaymentController::class, 'webhook'])->name('payment.webhook');

// Guest reservation routes (no auth required)
Route::prefix('guest')->name('guest.')->group(function () {
    Route::get('reservation/create', [\App\Http\Controllers\GuestReservationController::class, 'create'])->name('reservation.create');
    Route::post('reservation/send-codes', [\App\Http\Controllers\GuestReservationController::class, 'sendVerificationCodes'])->name('reservation.send-codes');
    Route::post('reservation/verify-email', [\App\Http\Controllers\GuestReservationController::class, 'verifyEmailCode'])->name('reservation.verify-email');
    Route::post('reservation/verify-phone', [\App\Http\Controllers\GuestReservationController::class, 'verifyPhoneCode'])->name('reservation.verify-phone');
    Route::post('reservation/store', [\App\Http\Controllers\GuestReservationController::class, 'store'])->name('reservation.store');
    Route::get('reservation/{reservation}/payment', [\App\Http\Controllers\GuestReservationController::class, 'showPayment'])->name('reservation.payment');
});

// Promotion routes (no auth required)
Route::get('api/promotions/active', [\App\Http\Controllers\PromotionController::class, 'getActive'])->name('promotions.active');
Route::post('api/promotions/{promotion}/view', [\App\Http\Controllers\PromotionController::class, 'recordView'])->name('promotions.view');
Route::post('api/promotions/{promotion}/action', [\App\Http\Controllers\PromotionController::class, 'recordAction'])->name('promotions.action');

// Include API routes under the /api prefix using the 'api' middleware group
Route::prefix('api')->middleware('api')->group(function () {
    require __DIR__.'/api.php';
});

require __DIR__.'/auth.php';
