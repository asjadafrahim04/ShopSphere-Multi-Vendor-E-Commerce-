<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\WishlistController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\ReviewController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application.
| These routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group.
|
*/

// ===================== PUBLIC ROUTES =====================

// Test Route
Route::get('/test', function () {
    return response()->json([
        'message' => 'ShopSphere API is working!',
        'status' => 'success',
        'version' => '1.0.0'
    ]);
});

// ===== AUTHENTICATION ROUTES =====
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/firebase-login', [AuthController::class, 'firebaseLogin']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

// ===== PUBLIC PRODUCT ROUTES =====
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/search', [ProductController::class, 'search']);
Route::get('/products/category/{categoryId}', [ProductController::class, 'byCategory']);
Route::get('/products/{id}', [ProductController::class, 'show']);

// ===== PUBLIC CATEGORY ROUTES =====
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{id}', [CategoryController::class, 'show']);
Route::get('/categories/{id}/products', [CategoryController::class, 'products']);

// ===== PUBLIC VENDOR ROUTES =====
Route::get('/vendors', [VendorController::class, 'index']);
Route::get('/vendors/{id}', [VendorController::class, 'show']);
Route::get('/vendors/{id}/products', [VendorController::class, 'products']);

// ===== PUBLIC REVIEW ROUTES =====
Route::get('/products/{productId}/reviews', [ReviewController::class, 'index']);


// ===================== PROTECTED ROUTES (Require Authentication) =====================
Route::middleware('auth:sanctum')->group(function () {
    
    // ===== AUTH ROUTES =====
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    
    // ===== CART ROUTES =====
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add', [CartController::class, 'add']);
    Route::put('/cart/update/{id}', [CartController::class, 'update']);
    Route::delete('/cart/remove/{id}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::get('/cart/total', [CartController::class, 'total']);
    
    // ===== WISHLIST ROUTES =====
    Route::get('/wishlist', [WishlistController::class, 'index']);
    Route::post('/wishlist/add/{productId}', [WishlistController::class, 'add']);
    Route::delete('/wishlist/remove/{productId}', [WishlistController::class, 'remove']);
    Route::get('/wishlist/check/{productId}', [WishlistController::class, 'check']);
    
    // ===== ORDER ROUTES =====
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    Route::get('/orders/track/{id}', [OrderController::class, 'track']);
    
    // ===== REVIEW ROUTES =====
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::put('/reviews/{id}', [ReviewController::class, 'update']);
    Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);
    
    // ===== VENDOR ROUTES (Only for Vendors) =====
    Route::middleware(['vendor'])->prefix('vendor')->group(function () {
        // Dashboard
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
        Route::get('/sales-report', [VendorController::class, 'salesReport']);
        
        // Product Management
        Route::get('/products', [VendorController::class, 'products']);
        Route::post('/products', [VendorController::class, 'storeProduct']);
        Route::put('/products/{id}', [VendorController::class, 'updateProduct']);
        Route::delete('/products/{id}', [VendorController::class, 'deleteProduct']);
        
        // Order Management
        Route::get('/orders', [VendorController::class, 'orders']);
        Route::put('/orders/{id}/status', [VendorController::class, 'updateOrderStatus']);
        
        // Coupon Management
        Route::get('/coupons', [VendorController::class, 'coupons']);
        Route::post('/coupons', [VendorController::class, 'storeCoupon']);
        Route::put('/coupons/{id}', [VendorController::class, 'updateCoupon']);
        Route::delete('/coupons/{id}', [VendorController::class, 'deleteCoupon']);
    });
    
    // ===== ADMIN ROUTES (Only for Admins) =====
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        
        // User Management
        Route::get('/users', [AdminController::class, 'users']);
        Route::put('/users/{id}/role', [AdminController::class, 'updateRole']);
        Route::put('/users/{id}/status', [AdminController::class, 'updateStatus']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
        
        // Vendor Management
        Route::get('/vendors', [AdminController::class, 'vendors']);
        Route::get('/vendors/pending', [AdminController::class, 'pendingVendors']);
        Route::put('/vendors/{id}/approve', [AdminController::class, 'approveVendor']);
        Route::put('/vendors/{id}/suspend', [AdminController::class, 'suspendVendor']);
        
        // Category Management
        Route::post('/categories', [CategoryController::class, 'store']);
        Route::put('/categories/{id}', [CategoryController::class, 'update']);
        Route::delete('/categories/{id}', [CategoryController::class, 'destroy']);
        
        // Order Management
        Route::get('/orders', [AdminController::class, 'orders']);
        Route::put('/orders/{id}/status', [AdminController::class, 'updateOrderStatus']);
        
        // Payment Management
        Route::get('/payments', [AdminController::class, 'payments']);
        Route::get('/payments/{id}', [AdminController::class, 'paymentDetails']);
    });
});