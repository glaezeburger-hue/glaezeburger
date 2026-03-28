<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transaction_item_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variation_option_id')->nullable()->constrained()->nullOnDelete();
            $table->string('variation_name');
            $table->string('option_name');
            $table->decimal('price_modifier', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaction_item_variations');
    }
};
