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
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\CouponController;
use App\Http\Controllers\Api\ShippingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
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
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
Route::post('/resend-verification', [AuthController::class, 'resendVerification']);

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

// ===== PUBLIC COUPON ROUTES =====
Route::get('/coupons/validate', [CouponController::class, 'validate']);


// ===================== PROTECTED ROUTES (Require Authentication) =====================
Route::middleware('auth:sanctum')->group(function () {
    
    // ===== AUTH ROUTES =====
    Route::get('/user', function (Request $request) {
        return response()->json([
            'success' => true,
            'data' => $request->user()->load(['vendor', 'cart'])
        ]);
    });
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::delete('/profile', [AuthController::class, 'deleteAccount']);
    
    // ===== CART ROUTES =====
    Route::prefix('cart')->group(function () {
        Route::get('/', [CartController::class, 'index']);
        Route::post('/add', [CartController::class, 'add']);
        Route::put('/update/{id}', [CartController::class, 'update']);
        Route::delete('/remove/{id}', [CartController::class, 'remove']);
        Route::delete('/clear', [CartController::class, 'clear']);
        Route::get('/total', [CartController::class, 'total']);
        Route::get('/count', [CartController::class, 'count']);
        Route::post('/apply-coupon', [CartController::class, 'applyCoupon']);
        Route::delete('/remove-coupon', [CartController::class, 'removeCoupon']);
    });
    
    // ===== WISHLIST ROUTES =====
    Route::prefix('wishlist')->group(function () {
        Route::get('/', [WishlistController::class, 'index']);
        Route::post('/add/{productId}', [WishlistController::class, 'add']);
        Route::delete('/remove/{productId}', [WishlistController::class, 'remove']);
        Route::get('/check/{productId}', [WishlistController::class, 'check']);
        Route::delete('/clear', [WishlistController::class, 'clear']);
        Route::get('/count', [WishlistController::class, 'count']);
    });
    
    // ===== ORDER ROUTES =====
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);
        Route::get('/{id}', [OrderController::class, 'show']);
        Route::post('/', [OrderController::class, 'store']);
        Route::put('/{id}/cancel', [OrderController::class, 'cancel']);
        Route::get('/track/{id}', [OrderController::class, 'track']);
        Route::post('/{id}/return', [OrderController::class, 'return']);
        Route::get('/invoice/{id}', [OrderController::class, 'invoice']);
    });
    
    // ===== PAYMENT ROUTES =====
    Route::prefix('payments')->group(function () {
        Route::post('/create', [PaymentController::class, 'create']);
        Route::post('/verify', [PaymentController::class, 'verify']);
        Route::get('/methods', [PaymentController::class, 'methods']);
        Route::get('/history', [PaymentController::class, 'history']);
        Route::post('/refund/{id}', [PaymentController::class, 'refund']);
    });
    
    // ===== REVIEW ROUTES =====
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);
        Route::put('/{id}', [ReviewController::class, 'update']);
        Route::delete('/{id}', [ReviewController::class, 'destroy']);
        Route::post('/{id}/helpful', [ReviewController::class, 'markHelpful']);
        Route::post('/{id}/report', [ReviewController::class, 'report']);
    });
    
    // ===== SHIPPING ROUTES =====
    Route::prefix('shipping')->group(function () {
        Route::get('/methods', [ShippingController::class, 'methods']);
        Route::post('/calculate', [ShippingController::class, 'calculate']);
        Route::get('/track/{trackingNumber}', [ShippingController::class, 'track']);
    });
    
    // ===== COUPON ROUTES =====
    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'index']);
        Route::get('/{code}', [CouponController::class, 'show']);
        Route::post('/apply', [CouponController::class, 'apply']);
    });
    
    // ===== VENDOR ROUTES (Only for Vendors) =====
    Route::middleware(['vendor'])->prefix('vendor')->group(function () {
        // Dashboard
        Route::get('/dashboard', [VendorController::class, 'dashboard']);
        Route::get('/stats', [VendorController::class, 'stats']);
        Route::get('/sales-report', [VendorController::class, 'salesReport']);
        Route::get('/earnings', [VendorController::class, 'earnings']);
        
        // Profile Management
        Route::get('/profile', [VendorController::class, 'profile']);
        Route::put('/profile', [VendorController::class, 'updateProfile']);
        Route::post('/profile/logo', [VendorController::class, 'uploadLogo']);
        
        // Product Management
        Route::prefix('products')->group(function () {
            Route::get('/', [ProductController::class, 'vendorProducts']);
            Route::post('/', [ProductController::class, 'store']);
            Route::put('/{id}', [ProductController::class, 'update']);
            Route::delete('/{id}', [ProductController::class, 'destroy']);
            Route::delete('/image/{imageId}', [ProductController::class, 'deleteImage']);
            Route::get('/low-stock', [ProductController::class, 'lowStock']);
            Route::put('/{id}/stock', [ProductController::class, 'updateStock']);
            Route::put('/{id}/toggle-status', [ProductController::class, 'toggleStatus']);
            Route::post('/{id}/duplicate', [ProductController::class, 'duplicate']);
        });
        
        // Order Management
        Route::prefix('orders')->group(function () {
            Route::get('/', [VendorController::class, 'orders']);
            Route::get('/{id}', [VendorController::class, 'orderDetails']);
            Route::put('/{id}/status', [VendorController::class, 'updateOrderStatus']);
            Route::put('/{id}/accept', [VendorController::class, 'acceptOrder']);
            Route::put('/{id}/reject', [VendorController::class, 'rejectOrder']);
            Route::get('/analytics', [VendorController::class, 'orderAnalytics']);
        });
        
        // Coupon Management
        Route::prefix('coupons')->group(function () {
            Route::get('/', [VendorController::class, 'coupons']);
            Route::post('/', [VendorController::class, 'storeCoupon']);
            Route::put('/{id}', [VendorController::class, 'updateCoupon']);
            Route::delete('/{id}', [VendorController::class, 'deleteCoupon']);
            Route::put('/{id}/toggle', [VendorController::class, 'toggleCoupon']);
        });
        
        // Payouts
        Route::prefix('payouts')->group(function () {
            Route::get('/', [VendorController::class, 'payouts']);
            Route::post('/request', [VendorController::class, 'requestPayout']);
            Route::get('/history', [VendorController::class, 'payoutHistory']);
        });
        
        // Analytics
        Route::prefix('analytics')->group(function () {
            Route::get('/products', [VendorController::class, 'productAnalytics']);
            Route::get('/orders', [VendorController::class, 'orderAnalytics']);
            Route::get('/customers', [VendorController::class, 'customerAnalytics']);
            Route::get('/revenue', [VendorController::class, 'revenueAnalytics']);
        });
    });
    
    // ===== ADMIN ROUTES (Only for Admins) =====
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        // Dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/analytics', [AdminController::class, 'analytics']);
        Route::get('/overview', [AdminController::class, 'overview']);
        
        // User Management
        Route::prefix('users')->group(function () {
            Route::get('/', [AdminController::class, 'users']);
            Route::get('/{id}', [AdminController::class, 'userDetails']);
            Route::put('/{id}/role', [AdminController::class, 'updateRole']);
            Route::put('/{id}/status', [AdminController::class, 'updateStatus']);
            Route::delete('/{id}', [AdminController::class, 'deleteUser']);
            Route::post('/', [AdminController::class, 'createUser']);
            Route::put('/{id}', [AdminController::class, 'updateUser']);
        });
        
        // Vendor Management
        Route::prefix('vendors')->group(function () {
            Route::get('/', [AdminController::class, 'vendors']);
            Route::get('/pending', [AdminController::class, 'pendingVendors']);
            Route::get('/{id}', [AdminController::class, 'vendorDetails']);
            Route::put('/{id}/approve', [AdminController::class, 'approveVendor']);
            Route::put('/{id}/suspend', [AdminController::class, 'suspendVendor']);
            Route::put('/{id}/commission', [AdminController::class, 'updateCommission']);
            Route::delete('/{id}', [AdminController::class, 'deleteVendor']);
            Route::get('/{id}/analytics', [AdminController::class, 'vendorAnalytics']);
        });
        
        // Category Management
        Route::prefix('categories')->group(function () {
            Route::post('/', [CategoryController::class, 'store']);
            Route::put('/{id}', [CategoryController::class, 'update']);
            Route::delete('/{id}', [CategoryController::class, 'destroy']);
            Route::put('/{id}/reorder', [CategoryController::class, 'reorder']);
            Route::post('/{id}/image', [CategoryController::class, 'uploadImage']);
        });
        
        // Product Management (Admin Override)
        Route::prefix('products')->group(function () {
            Route::get('/', [AdminController::class, 'products']);
            Route::get('/{id}', [AdminController::class, 'productDetails']);
            Route::put('/{id}/status', [AdminController::class, 'updateProductStatus']);
            Route::put('/{id}/feature', [AdminController::class, 'featureProduct']);
            Route::delete('/{id}', [AdminController::class, 'deleteProduct']);
            Route::get('/pending', [AdminController::class, 'pendingProducts']);
        });
        
        // Order Management (Admin Override)
        Route::prefix('orders')->group(function () {
            Route::get('/', [AdminController::class, 'orders']);
            Route::get('/{id}', [AdminController::class, 'orderDetails']);
            Route::put('/{id}/status', [AdminController::class, 'updateOrderStatus']);
            Route::delete('/{id}', [AdminController::class, 'deleteOrder']);
            Route::get('/analytics', [AdminController::class, 'orderAnalytics']);
        });
        
        // Payment Management
        Route::prefix('payments')->group(function () {
            Route::get('/', [AdminController::class, 'payments']);
            Route::get('/{id}', [AdminController::class, 'paymentDetails']);
            Route::put('/{id}/status', [AdminController::class, 'updatePaymentStatus']);
            Route::post('/{id}/refund', [AdminController::class, 'refundPayment']);
            Route::get('/analytics', [AdminController::class, 'paymentAnalytics']);
        });
        
        // Coupon Management (Admin)
        Route::prefix('coupons')->group(function () {
            Route::get('/', [AdminController::class, 'coupons']);
            Route::post('/', [AdminController::class, 'storeCoupon']);
            Route::put('/{id}', [AdminController::class, 'updateCoupon']);
            Route::delete('/{id}', [AdminController::class, 'deleteCoupon']);
            Route::put('/{id}/toggle', [AdminController::class, 'toggleCoupon']);
        });
        
        // System Settings
        Route::prefix('settings')->group(function () {
            Route::get('/', [AdminController::class, 'settings']);
            Route::put('/', [AdminController::class, 'updateSettings']);
            Route::put('/payment', [AdminController::class, 'updatePaymentSettings']);
            Route::put('/shipping', [AdminController::class, 'updateShippingSettings']);
            Route::put('/email', [AdminController::class, 'updateEmailSettings']);
        });
        
        // Reports
        Route::prefix('reports')->group(function () {
            Route::get('/sales', [AdminController::class, 'salesReport']);
            Route::get('/revenue', [AdminController::class, 'revenueReport']);
            Route::get('/products', [AdminController::class, 'productReport']);
            Route::get('/users', [AdminController::class, 'userReport']);
            Route::get('/export', [AdminController::class, 'exportReport']);
        });
    });
});

// ===================== FALLBACK ROUTE =====================
Route::fallback(function () {
    return response()->json([
        'success' => false,
        'message' => 'Route not found. Please check the URL and method.'
    ], 404);
});