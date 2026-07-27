<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id', 'category_id', 'name', 'slug', 'description',
        'price', 'compare_price', 'stock_quantity', 'low_stock_threshold',
        'sku', 'barcode', 'attributes', 'rating', 'reviews_count',
        'status', 'is_featured', 'is_new', 'discount_percentage',
        'discount_start', 'discount_end', 'view_count', 'sold_count'
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
    ];

    // ===== RELATIONSHIPS =====
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // ===== ACCESSORS =====
    public function getDiscountedPriceAttribute()
    {
        if ($this->discount_percentage && $this->discount_start <= now() && $this->discount_end >= now()) {
            return $this->price - ($this->price * $this->discount_percentage / 100);
        }
        return $this->price;
    }

    public function getIsOnSaleAttribute()
    {
        return $this->discount_percentage && 
               $this->discount_start <= now() && 
               $this->discount_end >= now();
    }

    public function getStockStatusAttribute()
    {
        if ($this->stock_quantity <= 0) {
            return 'out_of_stock';
        }
        if ($this->stock_quantity <= $this->low_stock_threshold) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    // ===== SCOPES =====
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }
}