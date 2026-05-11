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
        Schema::table('raw_materials', function (Blueprint $table) {
            // 1. Drop the old global unique SKU index
            // Laravel default naming convention: {table}_{column}_unique
            $table->dropUnique(['sku']);
            
            // 2. Add a new composite unique index: sku + branch_id
            $table->unique(['sku', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $table->dropUnique(['sku', 'branch_id']);
            $table->unique('sku');
        });
    }
};
