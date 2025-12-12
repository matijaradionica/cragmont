<?php

use App\Http\Controllers\AscentController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\CommentReportController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\RatingController;
use App\Http\Controllers\RouteController;
use App\Http\Controllers\RouteTopoController;
use App\Http\Controllers\WarningController;
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

    Route::get('routes/{route}/topo', [RouteTopoController::class, 'show'])
        ->name('routes.topo');

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

// Rating and Comment routes
Route::middleware('auth')->group(function () {
    Route::post('routes/{route}/rate', [RatingController::class, 'store'])->name('routes.rate');
    Route::post('routes/{route}/comments', [CommentController::class, 'store'])->name('routes.comments.store');
    Route::put('comments/{comment}', [CommentController::class, 'update'])->name('comments.update');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');
    Route::post('comments/{comment}/vote', [CommentController::class, 'vote'])->name('comments.vote');
    Route::post('comments/{comment}/report', [CommentReportController::class, 'store'])->name('comments.report');
});

// User warnings routes
Route::middleware('auth')->group(function () {
    Route::get('warnings', [WarningController::class, 'index'])->name('warnings.index');
    Route::post('warnings/{warning}/mark-as-read', [WarningController::class, 'markAsRead'])->name('warnings.mark-as-read');
    Route::post('warnings/mark-all-as-read', [WarningController::class, 'markAllAsRead'])->name('warnings.mark-all-as-read');
});

// Admin routes - only accessible by admins
Route::middleware(['auth', 'can:viewAdmin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('routes/bulk-approve', [DashboardController::class, 'bulkApprove'])->name('routes.bulk-approve');

    // User role management
    Route::get('users', [UserRoleController::class, 'index'])->name('users.index');
    Route::patch('users/{user}/role', [UserRoleController::class, 'updateRole'])->name('users.update-role');

    // Comment reports
    Route::get('reports', [CommentReportController::class, 'index'])->name('reports.index');
    Route::post('reports/{report}/approve', [CommentReportController::class, 'approve'])->name('reports.approve');
    Route::post('reports/{report}/dismiss', [CommentReportController::class, 'dismiss'])->name('reports.dismiss');
});

require __DIR__.'/auth.php';
