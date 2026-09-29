<?php

use App\Http\Controllers\BookingCartController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/room', [PageController::class, 'room'])->name('room');
Route::get('/room/{room}', [PageController::class, 'roomDetail'])->name('room.detail');
Route::get('/activity', [PageController::class, 'activity'])->name('activity');
Route::get('/spa', [PageController::class, 'spa'])->name('spa');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/dining/resto-amed-cafe', [PageController::class, 'restoAmedCafe'])->name('dining.resto-amed-cafe');
Route::get('/dining/barak-rooftop-and-bar', [PageController::class, 'barakRooftopAndBar'])->name('dining.barak-rooftop-and-bar');

// Public, session-backed booking cart (Stage 3b) — no existing public POST route to
// mirror a throttle convention from (every prior public route is GET-only), so this uses
// Laravel's own standard throttle middleware. Stays registered (Booking Feature Flag task
// says do not delete booking code) but 404s while the flag is off — see
// EnsureBookingEnabled.
Route::middleware(['throttle:30,1', 'booking.enabled'])->prefix('booking/cart')->name('booking.cart.')->group(function () {
    Route::post('items', [BookingCartController::class, 'store'])->name('items.store');
    Route::delete('items/{ratePlan}', [BookingCartController::class, 'destroy'])->name('items.destroy');
    Route::post('dates', [BookingCartController::class, 'updateDates'])->name('dates.update');
    Route::post('checkout', [BookingCartController::class, 'checkout'])->name('checkout');
});

require __DIR__.'/admin.php';
