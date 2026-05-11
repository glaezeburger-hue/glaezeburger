<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');                          // "Centra Niaga Square"
            $table->string('code', 10)->unique();            // "CNS" — invoice prefix
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('phone')->nullable();
            $table->string('receipt_header')->nullable();     // Custom struk header per cabang
            $table->string('receipt_footer')->nullable();     // Custom struk footer per cabang
            $table->string('receipt_instagram')->nullable();  // IG handle per cabang
            $table->string('receipt_tiktok')->nullable();     // TikTok handle per cabang
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
