<?php

use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Api\Admin\KosController as AdminKosController;
use App\Http\Controllers\Api\Admin\OwnerController as AdminOwnerController;
use App\Http\Controllers\Api\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FacilityController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\KosController;
use App\Http\Controllers\Api\Owner\DashboardController as OwnerDashboardController;
use App\Http\Controllers\Api\Owner\KosController as OwnerKosController;
use App\Http\Controllers\Api\Owner\PhotoController as OwnerPhotoController;
use App\Http\Controllers\Api\Owner\StockController as OwnerStockController;
use App\Http\Controllers\Api\PasswordResetController;
use App\Http\Controllers\Api\ReviewController;
use App\Models\Kos;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Publik (guest & login)
Route::get('/kos', [KosController::class, 'index']);
Route::get('/kos/{slug}', [KosController::class, 'show']);
Route::get('/facilities', [FacilityController::class, 'index']);

Route::middleware('throttle:20,1')->group(function () {
    Route::post('/kos/{slug}/wa-click', [KosController::class, 'trackWaClick']);
});

// Auth
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
    Route::post('/reset-password', [PasswordResetController::class, 'reset']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/role', [AuthController::class, 'chooseRole']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Mahasiswa
    Route::post('/kos/{slug}/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{review}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{review}', [ReviewController::class, 'destroy']);

    Route::get('/favorites', [FavoriteController::class, 'index']);
    Route::post('/kos/{slug}/favorite', [FavoriteController::class, 'store']);
    Route::delete('/kos/{slug}/favorite', [FavoriteController::class, 'destroy']);

    // Pemilik kos
    Route::can('accessOwnerArea', Kos::class)->group(function () {
        Route::get('/owner/dashboard', OwnerDashboardController::class);
        Route::apiResource('owner/kos', OwnerKosController::class)->parameters(['kos' => 'kos'])->except(['edit', 'create']);
        Route::put('/owner/kos/{kos}/stock', [OwnerStockController::class, 'update']);
        Route::post('/owner/kos/{kos}/photos', [OwnerPhotoController::class, 'store']);
        Route::post('/owner/kos/{kos}/photos/order', [OwnerPhotoController::class, 'reorder']);
        Route::put('/owner/photos/{photo}', [OwnerPhotoController::class, 'update']);
        Route::delete('/owner/photos/{photo}', [OwnerPhotoController::class, 'destroy']);
    });

    // Admin
    Route::prefix('admin')->can('moderate', User::class)->group(function () {
        Route::get('/dashboard', AdminDashboardController::class);

        Route::get('/owners', [AdminOwnerController::class, 'index']);
        Route::post('/owners/{user}/verify', [AdminOwnerController::class, 'verify']);
        Route::post('/owners/{user}/reject', [AdminOwnerController::class, 'reject']);

        Route::get('/kos', [AdminKosController::class, 'index']);
        Route::post('/kos/{kos}/approve', [AdminKosController::class, 'approve']);
        Route::post('/kos/{kos}/reject', [AdminKosController::class, 'reject']);
        Route::post('/kos/{kos}/hide', [AdminKosController::class, 'hide']);

        Route::get('/reviews', [AdminReviewController::class, 'index']);
        Route::post('/reviews/{review}/hide', [AdminReviewController::class, 'hide']);
        Route::post('/reviews/{review}/unhide', [AdminReviewController::class, 'unhide']);
        Route::delete('/reviews/{review}', [AdminReviewController::class, 'destroy']);

        Route::apiResource('facilities', AdminFacilityController::class)->except(['edit', 'create', 'show']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);
    });
});
