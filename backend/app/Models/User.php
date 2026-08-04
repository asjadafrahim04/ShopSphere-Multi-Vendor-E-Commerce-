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

    // Add product relationship for vendor
    public function products()
    {
        return $this->hasMany(Product::class, 'vendor_id');
    }

    // ===== HELPER METHODS =====
    /**
     * Check if user is a vendor (with approval check)
     */
    public function isVendor()
    {
        return $this->role === 'vendor' && $this->vendor && $this->vendor->is_approved;
    }

    /**
     * Check if user has vendor role (without approval check)
     * Use this for route middleware
     */
    public function hasVendorRole()
    {
        return $this->role === 'vendor' || $this->role === 'admin';
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
    public function getOrCreateCart()
    {
        return $this->cart()->firstOrCreate([]);
    }

    public function getCartCount()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return 0;
        }
        return $cart->items()->sum('quantity');
    }

    public function getCartTotal()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return 0;
        }
        return $cart->items()->sum(\DB::raw('quantity * price'));
    }

    public function clearCart()
    {
        $cart = $this->cart()->first();
        if ($cart) {
            $cart->items()->delete();
            return true;
        }
        return false;
    }

    public function hasCartItems()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return false;
        }
        return $cart->items()->count() > 0;
    }

    public function getCartItems()
    {
        $cart = $this->cart()->first();
        if (!$cart) {
            return collect();
        }
        return $cart->items()->with('product')->get();
    }
}