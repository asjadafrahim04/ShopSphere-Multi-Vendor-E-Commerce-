<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'vendor_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'discount_percentage',
        'discount_start',
        'discount_end',
        'stock_quantity',
        'low_stock_threshold',
        'sku',
        'barcode',
        'attributes',
        'specifications',
        'meta_data',
        'rating',
        'reviews_count',
        'status',
        'is_active',
        'is_featured',
        'is_new',
        'is_approved',
        'is_best_seller',
        'view_count',
        'sold_count',
        'wishlist_count',
        'weight',
        'length',
        'width',
        'height',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'published_at',
        'featured_until',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'rating' => 'float',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_approved' => 'boolean',
        'is_best_seller' => 'boolean',
        'discount_start' => 'datetime',
        'discount_end' => 'datetime',
        'published_at' => 'datetime',
        'featured_until' => 'datetime',
        'attributes' => 'array',
        'specifications' => 'array',
        'meta_data' => 'array',
        'reviews_count' => 'integer',
        'view_count' => 'integer',
        'sold_count' => 'integer',
        'wishlist_count' => 'integer',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    // ===== RELATIONSHIPS =====

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function carts(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    // ===== SCOPES =====

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_quantity', '>', 0);
    }

    public function scopeLowStock($query, $threshold = 5)
    {
        return $query->where('stock_quantity', '<=', $threshold);
    }

    public function scopeOnSale($query)
    {
        return $query->whereNotNull('discount_percentage')
            ->where('discount_percentage', '>', 0)
            ->where(function($q) {
                $q->whereNull('discount_start')
                  ->orWhere('discount_start', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('discount_end')
                  ->orWhere('discount_end', '>=', now());
            });
    }

    // ===== ACCESSORS =====

    public function getDiscountPriceAttribute(): ?float
    {
        if ($this->discount_percentage && $this->is_on_sale) {
            return $this->price - ($this->price * ($this->discount_percentage / 100));
        }
        return null;
    }

    public function getIsOnSaleAttribute(): bool
    {
        if (!$this->discount_percentage || $this->discount_percentage <= 0) {
            return false;
        }

        if ($this->discount_start && $this->discount_start > now()) {
            return false;
        }

        if ($this->discount_end && $this->discount_end < now()) {
            return false;
        }

        return true;
    }

    public function getPrimaryImageAttribute()
    {
        return $this->images->where('is_primary', true)->first() 
            ?? $this->images->first();
    }

    public function getGalleryImagesAttribute()
    {
        return $this->images->where('is_primary', false);
    }

    // ===== HELPERS =====

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->low_stock_threshold;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    // ✅ ADDED: Check if product has enough stock
    public function hasStock($quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    // ✅ ADDED: Decrease stock quantity
    public function decreaseStock($quantity = 1): bool
    {
        if ($this->stock_quantity >= $quantity) {
            $this->stock_quantity -= $quantity;
            $this->save();
            return true;
        }
        return false;
    }

    // ✅ ADDED: Increase stock quantity
    public function increaseStock($quantity = 1): bool
    {
        $this->stock_quantity += $quantity;
        $this->save();
        return true;
    }

    public function getStockStatus(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        }
        if ($this->isLowStock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusLabel(): string
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'Out of Stock',
            'low_stock' => 'Low Stock',
            default => 'In Stock',
        };
    }

    public function getStockStatusColor(): string
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'red',
            'low_stock' => 'orange',
            default => 'green',
        };
    }

    public function getAverageRating(): float
    {
        return $this->rating ?? 0;
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->price, 2);
    }

    public function getFormattedDiscountPrice(): ?string
    {
        if ($discountPrice = $this->discount_price) {
            return '$' . number_format($discountPrice, 2);
        }
        return null;
    }

    public function getDiscountPercentageFormatted(): ?string
    {
        if ($this->discount_percentage) {
            return $this->discount_percentage . '%';
        }
        return null;
    }
}