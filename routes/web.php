<?php

use App\Http\Controllers\AscentController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserRoleController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

// Location routes - authenticated users can view, admin/moderator can manage
Route::middleware('auth')->group(function () {
    Route::resource('locations', LocationController::class);
});

// Route routes - authenticated users can view and create, policies control edit/delete
Route::middleware('auth')->group(function () {
    Route::resource('routes', RouteController::class);

    // Additional route management actions
    Route::post('routes/{route}/approve', [RouteController::class, 'approve'])
        ->name('routes.approve')
        ->middleware('can:approve,route');

    Route::post('routes/{route}/reject', [RouteController::class, 'reject'])
        ->name('routes.reject')
        ->middleware('can:approve,route');
});

// Ascent (Logbook) routes - authenticated users can manage their own ascents
Route::middleware('auth')->group(function () {
    Route::resource('ascents', AscentController::class);
});

// Admin routes - only accessible by admins
Route::middleware(['auth', 'can:viewAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('routes/bulk-approve', [DashboardController::class, 'bulkApprove'])->name('routes.bulk-approve');

    // User role management
    Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [UserRoleController::class, 'updateRole'])->name('users.update-role');
});

require __DIR__.'/auth.php';
