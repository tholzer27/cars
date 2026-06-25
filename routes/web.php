<?php

use App\Http\Controllers\AdminCleaningPackagesController;
use App\Http\Controllers\AdminCleaningServicesController;
use App\Http\Controllers\AdminWorkingHoursController;
use App\Http\Controllers\BookingController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');
Route::inertia('agbs', 'legal/Terms')->name('terms');
Route::inertia('impressum', 'legal/Imprint')->name('imprint');

Route::get('booking', [BookingController::class, 'create'])->name('booking');
Route::get('bookings/catalog', [BookingController::class, 'index'])->name('bookings.catalog');
Route::post('bookings', [BookingController::class, 'store'])->name('bookings.store');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', '/booking')->name('dashboard');
    Route::get('admin/working-hours', [AdminWorkingHoursController::class, 'edit'])->name('admin.working-hours.edit');
    Route::put('admin/working-hours', [AdminWorkingHoursController::class, 'update'])->name('admin.working-hours.update');
    Route::get('admin/services', [AdminCleaningServicesController::class, 'edit'])->name('admin.services.edit');
    Route::put('admin/services', [AdminCleaningServicesController::class, 'update'])->name('admin.services.update');
    Route::get('admin/packages', [AdminCleaningPackagesController::class, 'edit'])->name('admin.packages.edit');
    Route::put('admin/packages', [AdminCleaningPackagesController::class, 'update'])->name('admin.packages.update');
});

require __DIR__.'/settings.php';
