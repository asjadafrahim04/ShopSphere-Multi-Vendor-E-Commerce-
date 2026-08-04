<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('shop_name');
            $table->string('shop_slug')->unique()->nullable();
            $table->text('shop_description')->nullable();
            $table->string('shop_logo')->nullable();
            $table->string('shop_banner')->nullable();
            $table->string('business_category')->nullable();
            $table->text('business_address')->nullable();
            $table->string('business_phone')->nullable();
            $table->string('business_email')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendors');
    }
};