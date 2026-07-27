<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'is_active',
        'firebase_uid',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // ===== RELATIONSHIPS =====
    public function vendor()
    {
        return $this->hasOne(Vendor::class);
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ===== HELPER METHODS =====
    public function isVendor()
    {
        return $this->role === 'vendor' && $this->vendor && $this->vendor->is_approved;
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    // ===== CART HELPER METHODS =====
    /**
     * Get or create cart for the user
     */
    public function getOrCreateCart()
    {
        return $this->cart()->firstOrCreate([]);
    }

    /**
     * Get cart count (total items)
     */
    public function getCartCount()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return 0;
        }
        return $cart->items()->sum('quantity');
    }

    /**
     * Get cart total
     */
    public function getCartTotal()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return 0;
        }
        return $cart->items()->sum(\DB::raw('quantity * price'));
    }

    /**
     * Clear user's cart
     */
    public function clearCart()
    {
        $cart = $this->cart()->first();
        if ($cart) {
            $cart->items()->delete();
            return true;
        }
        return false;
    }

    /**
     * Check if user has items in cart
     */
    public function hasCartItems()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return false;
        }
        return $cart->items()->count() > 0;
    }

    /**
     * Get user's cart items with product details
     */
    public function getCartItems()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return collect();
        }
        return $cart->items()->with('product')->get();
    }
}