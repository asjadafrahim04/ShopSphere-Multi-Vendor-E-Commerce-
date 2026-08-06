<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'type',
        'value',
        'min_order_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'start_date',
        'end_date',
        'vendor_id',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'used_count' => 'integer',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function isValid($subtotal = 0): bool
    {
        if (!$this->is_active) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($this->start_date && $this->start_date > now()) return false;
        if ($this->end_date && $this->end_date < now()) return false;
        if ($subtotal < $this->min_order_amount) return false;
        return true;
    }

    public function calculateDiscount($subtotal): array
    {
        $discountAmount = 0;

        if ($this->type === 'percentage') {
            $discountAmount = $subtotal * ($this->value / 100);
            if ($this->max_discount_amount && $discountAmount > $this->max_discount_amount) {
                $discountAmount = $this->max_discount_amount;
            }
        } else {
            $discountAmount = min($this->value, $subtotal);
        }

        return [
            'discount_amount' => round($discountAmount, 2),
            'final_total' => max(0, $subtotal - $discountAmount),
        ];
    }
}