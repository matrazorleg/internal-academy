<?php

use App\Http\Controllers\Admin\WorkshopController as AdminWorkshopController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::middleware('role:employee')->group(function (): void {
        Route::post('/workshops/{workshop}/registrations', [RegistrationController::class, 'store'])
            ->name('workshops.registrations.store');
        Route::delete('/workshops/{workshop}/registrations', [RegistrationController::class, 'destroy'])
            ->name('workshops.registrations.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->group(function (): void {
        Route::get('/workshops/stats', [AdminWorkshopController::class, 'stats'])->name('workshops.stats');
        Route::resource('workshops', AdminWorkshopController::class)->except(['show']);
    });
});
