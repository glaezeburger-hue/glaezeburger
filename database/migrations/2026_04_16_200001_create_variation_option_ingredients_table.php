<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('variation_option_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('variation_option_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->constrained()->cascadeOnDelete();
            $table->enum('action', ['exclude'])->default('exclude'); // Expandable for Future (e.g. 'include', 'replace')
            $table->timestamps();
            
            $table->unique(['variation_option_id', 'raw_material_id'], 'var_opt_raw_mat_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('variation_option_ingredients');
    }
};
