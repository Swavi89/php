<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AdminController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::get('/health', function () {
    return response()->json([
        'success' => true,
        'message' => 'JWT E-Commerce API is running',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// ==================== AUTHENTICATION ROUTES ====================
Route::prefix('auth')->group(function () {
    // Public authentication routes
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    
    // Protected authentication routes
    Route::middleware(['jwt.auth', 'status'])->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
    });
});

// ==================== PRODUCT ROUTES ====================
Route::prefix('products')->group(function () {
    // Public product routes
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{id}', [ProductController::class, 'show']);
    
    // Protected product routes
    Route::middleware(['jwt.auth', 'status'])->group(function () {
        Route::post('/', [ProductController::class, 'store'])
            ->middleware('role:vendor,admin');
        Route::put('/{id}', [ProductController::class, 'update']);
        Route::delete('/{id}', [ProductController::class, 'destroy']);
        
        // Get reviews for a product
        Route::get('/{productId}/reviews', [ReviewController::class, 'index']);
        Route::post('/{productId}/reviews', [ReviewController::class, 'store']);
    });
});

// ==================== VENDOR ROUTES ====================
Route::prefix('vendor')->middleware(['jwt.auth', 'status', 'role:vendor,admin'])->group(function () {
    Route::get('/products', [ProductController::class, 'vendorProducts']);
    Route::get('/orders', [OrderController::class, 'index']);
});

// ==================== ORDER ROUTES ====================
Route::prefix('orders')->middleware(['jwt.auth', 'status'])->group(function () {
    Route::get('/', [OrderController::class, 'index']);
    Route::post('/', [OrderController::class, 'store']);
    Route::get('/{id}', [OrderController::class, 'show']);
    Route::put('/{id}/status', [OrderController::class, 'updateStatus'])
        ->middleware('role:vendor,admin');
    Route::delete('/{id}', [OrderController::class, 'destroy']);
});

// ==================== REVIEW ROUTES ====================
Route::prefix('reviews')->middleware(['jwt.auth', 'status'])->group(function () {
    Route::put('/{id}', [ReviewController::class, 'update']);
    Route::delete('/{id}', [ReviewController::class, 'destroy']);
});

// ==================== ADMIN ROUTES ====================
Route::prefix('admin')->middleware(['jwt.auth', 'status', 'role:admin'])->group(function () {
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::get('/statistics', [AdminController::class, 'getStatistics']);
});

// Fallback route for undefined API endpoints
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Endpoint not found',
    ], 404);
});
