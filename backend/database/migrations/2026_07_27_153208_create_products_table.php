<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->foreignId('vendor_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            
            // Basic Product Info
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            
            // Pricing
            $table->decimal('price', 10, 2);
            $table->decimal('compare_price', 10, 2)->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->timestamp('discount_start')->nullable();
            $table->timestamp('discount_end')->nullable();
            
            // Inventory
            $table->integer('stock_quantity')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->nullable();
            
            // Attributes & Meta
            $table->json('attributes')->nullable();
            $table->json('specifications')->nullable();
            $table->json('meta_data')->nullable();
            
            // Ratings & Reviews
            $table->float('rating')->default(0);
            $table->integer('reviews_count')->default(0);
            
            // Status & Flags
            $table->enum('status', ['draft', 'pending', 'active', 'inactive'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_new')->default(false);
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_best_seller')->default(false);
            
            // Statistics
            $table->integer('view_count')->default(0);
            $table->integer('sold_count')->default(0);
            $table->integer('wishlist_count')->default(0);
            
            // Weight & Dimensions (for shipping)
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            
            // Timestamps
            $table->timestamps();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('featured_until')->nullable();
            
            // Indexes for performance
            $table->index('vendor_id');
            $table->index('category_id');
            $table->index('slug');
            $table->index('sku');
            $table->index('status');
            $table->index('is_active');
            $table->index('is_featured');
            $table->index('is_new');
            $table->index('price');
            $table->index('created_at');
            $table->index(['is_active', 'status']);
            $table->index(['category_id', 'is_active']);
            $table->index(['vendor_id', 'is_active']);
            $table->index(['price', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};