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

    // ===== STOCK HELPER METHODS =====
    /**
     * Check if product has enough stock
     */
    public function hasStock($quantity = 1)
    {
        return $this->stock_quantity >= $quantity;
    }

    /**
     * Decrease stock when product is purchased
     */
    public function decreaseStock($quantity = 1)
    {
        if ($this->hasStock($quantity)) {
            $this->decrement('stock_quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Increase stock when product is returned or order cancelled
     */
    public function increaseStock($quantity = 1)
    {
        $this->increment('stock_quantity', $quantity);
        return true;
    }

    /**
     * Get the effective price (with discount if applicable)
     */
    public function getEffectivePrice()
    {
        return $this->is_on_sale ? $this->discounted_price : $this->price;
    }

    /**
     * Get product image URL or fallback
     */
    public function getImageUrl()
    {
        $primaryImage = $this->images()->where('is_primary', true)->first();
        return $primaryImage ? $primaryImage->image_url : null;
    }

    /**
     * Check if product is available for purchase
     */
    public function isAvailable()
    {
        return $this->status === 'active' && $this->stock_quantity > 0;
    }

    /**
     * Get product rating formatted
     */
    public function getRatingAttribute($value)
    {
        return round($value, 1);
    }

    /**
     * Get formatted price with currency symbol
     */
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    /**
     * Get formatted compare price with currency symbol
     */
    public function getFormattedComparePriceAttribute()
    {
        return $this->compare_price ? '$' . number_format($this->compare_price, 2) : null;
    }

    /**
     * Get formatted discounted price with currency symbol
     */
    public function getFormattedDiscountedPriceAttribute()
    {
        return '$' . number_format($this->discounted_price, 2);
    }

    /**
     * Get product name with vendor
     */
    public function getFullNameAttribute()
    {
        return $this->name . ' - ' . ($this->vendor->shop_name ?? '');
    }

    /**
     * Check if product is new (created within last 30 days)
     */
    public function getIsNewArrivalAttribute()
    {
        return $this->created_at >= now()->subDays(30);
    }

    /**
     * Get low stock alert
     */
    public function getLowStockAlertAttribute()
    {
        if ($this->stock_quantity <= $this->low_stock_threshold && $this->stock_quantity > 0) {
            return true;
        }
        return false;
    }

    /**
     * Get average rating from reviews
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews count
     */
    public function getTotalReviewsAttribute()
    {
        return $this->reviews()->count();
    }
}