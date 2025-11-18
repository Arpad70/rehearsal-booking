<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\RoomController;  
use App\Http\Controllers\DeviceController;  



Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    /** @var \App\Models\User $user */
    $user = auth()->user();
    $reservations = $user->reservations()
        ->with('room')
        ->where('end_at', '>', now())
        ->orderBy('start_at')
        ->get();
    return view('dashboard', compact('reservations'));
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
});

// Include API routes under the /api prefix using the 'api' middleware group
Route::prefix('api')->middleware('api')->group(function () {
    require __DIR__.'/api.php';
});

require __DIR__.'/auth.php';
