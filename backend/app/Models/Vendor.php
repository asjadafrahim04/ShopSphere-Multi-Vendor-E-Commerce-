<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'shop_name', 'shop_slug', 'shop_description',
        'shop_logo', 'shop_banner', 'business_category',
        'business_address', 'business_phone', 'business_email',
        'is_approved', 'is_active', 'bank_name',
        'bank_account_number', 'bank_account_holder', 'settings'
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    // ===== RELATIONSHIPS =====
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}