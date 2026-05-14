<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Add branch_id (nullable initially for existing rows)
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('id');
        });

        // 2. Backfill: assign all existing products to branch_id = 1
        DB::table('products')->update(['branch_id' => 1]);

        // 3. Make it NOT NULL and add Foreign Key
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // 4. Update unique constraints (sku and slug) to be branch-scoped
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->dropUnique('products_slug_unique');
            
            $table->unique(['sku', 'branch_id']);
            $table->unique(['slug', 'branch_id']);
            
            // Add index for performance
            $table->index('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku', 'branch_id']);
            $table->dropUnique(['slug', 'branch_id']);
            
            $table->unique('sku');
            $table->unique('slug');

            $table->dropForeign(['branch_id']);
            $table->dropIndex(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
