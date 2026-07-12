<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds a `cost_price` column to `transaction_items` to snapshot the product's
     * HPP (Harga Pokok Penjualan) at the time of each transaction.
     *
     * This prevents future changes to product cost_price from retroactively
     * altering historical financial reports (COGS, Gross Profit, Net Profit).
     *
     * Also backfills existing records with the current product cost_price
     * as a best-effort approximation.
     */
    public function up(): void
    {
        // 1. Add the column
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->decimal('cost_price', 15, 2)->default(0)->after('price')
                  ->comment('HPP snapshot at time of sale');
        });

        // 2. Backfill existing records with current product cost_price
        // This is a best-effort approximation since the original HPP was never stored.
        // Using a single UPDATE JOIN for efficiency (no N+1 queries).
        DB::statement('
            UPDATE transaction_items ti
            INNER JOIN products p ON ti.product_id = p.id
            SET ti.cost_price = p.cost_price
            WHERE ti.cost_price = 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });
    }
};
