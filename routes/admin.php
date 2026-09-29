<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AdminAuthController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\DiningItemController;
use App\Http\Controllers\Admin\DiningVenueController;
use App\Http\Controllers\Admin\DiningVenueImageController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\PageContentController;
use App\Http\Controllers\Admin\RatePlanController;
use App\Http\Controllers\Admin\RoomController;
use App\Http\Controllers\Admin\RoomImageController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AdminAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AdminAuthController::class, 'login'])->name('login.attempt');

    Route::middleware('admin')->group(function () {
        Route::post('logout', [AdminAuthController::class, 'logout'])->name('logout');
        Route::get('dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::resource('rooms', RoomController::class)->except(['show']);

        Route::post('rooms/{room}/images', [RoomImageController::class, 'store'])->name('rooms.images.store');
        Route::patch('rooms/{room}/images/{image}', [RoomImageController::class, 'update'])->name('rooms.images.update');
        Route::delete('rooms/{room}/images/{image}', [RoomImageController::class, 'destroy'])->name('rooms.images.destroy');

        Route::post('rooms/{room}/rate-plans', [RatePlanController::class, 'store'])->name('rooms.rate-plans.store');
        Route::patch('rooms/{room}/rate-plans/{ratePlan}', [RatePlanController::class, 'update'])->name('rooms.rate-plans.update');
        Route::delete('rooms/{room}/rate-plans/{ratePlan}', [RatePlanController::class, 'destroy'])->name('rooms.rate-plans.destroy');

        Route::resource('activities', ActivityController::class)->except(['show']);

        Route::resource('events', EventController::class)->except(['show']);

        Route::resource('dining', DiningVenueController::class)->only(['edit', 'destroy']);

        Route::post('dining/{dining}/images', [DiningVenueImageController::class, 'store'])->name('dining.images.store');
        Route::patch('dining/{dining}/images/{image}', [DiningVenueImageController::class, 'update'])->name('dining.images.update');
        Route::delete('dining/{dining}/images/{image}', [DiningVenueImageController::class, 'destroy'])->name('dining.images.destroy');

        Route::post('dining/{dining}/items', [DiningItemController::class, 'store'])->name('dining.items.store');
        Route::patch('dining/{dining}/items/{item}', [DiningItemController::class, 'update'])->name('dining.items.update');
        Route::delete('dining/{dining}/items/{item}', [DiningItemController::class, 'destroy'])->name('dining.items.destroy');

        Route::resource('pages', PageContentController::class)->only(['edit', 'update']);

        Route::resource('banners', BannerController::class)->only(['index', 'edit', 'update']);

        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');

        Route::resource('testimonials', TestimonialController::class)->except(['show']);
    });
});
