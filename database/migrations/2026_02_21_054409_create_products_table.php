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
        Schema::create('products', function (Blueprint $bit) {
            $bit->id();
            $bit->foreignId('category_id')->constrained()->onDelete('cascade');
            $bit->string('name');
            $bit->string('slug')->unique();
            $bit->string('sku')->unique();
            $bit->text('description')->nullable();
            $bit->decimal('cost_price', 15, 2);
            $bit->decimal('selling_price', 15, 2);
            $bit->integer('stock')->default(0);
            $bit->string('image_path')->nullable();
            $bit->boolean('is_active')->default(true);
            $bit->timestamps();
            $bit->softDeletes();
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
