<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'active', 'inactive'])->default('pending')->after('reviews_count');
            $table->boolean('is_featured')->default(false)->after('status');
            $table->boolean('is_new')->default(false)->after('is_featured');
            $table->decimal('discount_percentage', 5, 2)->nullable()->after('is_new');
            $table->timestamp('discount_start')->nullable()->after('discount_percentage');
            $table->timestamp('discount_end')->nullable()->after('discount_start');
            $table->integer('view_count')->default(0)->after('discount_end');
            $table->integer('sold_count')->default(0)->after('view_count');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'status', 
                'is_featured', 
                'is_new', 
                'discount_percentage', 
                'discount_start', 
                'discount_end', 
                'view_count', 
                'sold_count'
            ]);
        });
    }
};