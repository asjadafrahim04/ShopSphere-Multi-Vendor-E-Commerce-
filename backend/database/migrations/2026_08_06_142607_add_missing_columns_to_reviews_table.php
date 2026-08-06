<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            // Add rating column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'rating')) {
                $table->integer('rating')->default(5)->after('user_id');
            }
            
            // Add comment column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'comment')) {
                $table->text('comment')->nullable()->after('rating');
            }
            
            // Add is_verified column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'is_verified')) {
                $table->boolean('is_verified')->default(false)->after('comment');
            }
            
            // Add is_approved column if it doesn't exist
            if (!Schema::hasColumn('reviews', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('is_verified');
            }
            
            // Add product_id if it doesn't exist
            if (!Schema::hasColumn('reviews', 'product_id')) {
                $table->foreignId('product_id')->constrained()->onDelete('cascade')->after('id');
            }
            
            // Add user_id if it doesn't exist
            if (!Schema::hasColumn('reviews', 'user_id')) {
                $table->foreignId('user_id')->constrained()->onDelete('cascade')->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $columns = ['rating', 'comment', 'is_verified', 'is_approved', 'product_id', 'user_id'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};