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
        // 1. Add description to products table
        Schema::table('products', function (Blueprint $table) {
            $table->longText('description')->nullable()->after('subcategory');
        });

        // 2. Create product_images table for gallery
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('image_path');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 3. Create reviews table
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('customer_name');
            $table->integer('rating'); // 1-5
            $table->text('comment');
            $table->boolean('is_approved')->default(true); // Auto-approve for now
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('product_images');
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
