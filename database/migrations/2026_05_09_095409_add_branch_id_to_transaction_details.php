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
        // 1. Add branch_id to transaction_items
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('transaction_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // 2. Add branch_id to transaction_item_variations
        Schema::table('transaction_item_variations', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('transaction_item_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // 3. Add branch_id to transaction_item_addons
        Schema::table('transaction_item_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable()->after('transaction_item_id');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        // 4. Backfill data from parent transactions
        DB::statement("UPDATE transaction_items ti JOIN transactions t ON ti.transaction_id = t.id SET ti.branch_id = t.branch_id");
        DB::statement("UPDATE transaction_item_variations tiv JOIN transaction_items ti ON tiv.transaction_item_id = ti.id SET tiv.branch_id = ti.branch_id");
        DB::statement("UPDATE transaction_item_addons tia JOIN transaction_items ti ON tia.transaction_item_id = ti.id SET tia.branch_id = ti.branch_id");

        // 5. Make branch_id NOT NULL after backfill
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
        });
        Schema::table('transaction_item_variations', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
        });
        Schema::table('transaction_item_addons', function (Blueprint $table) {
            $table->unsignedBigInteger('branch_id')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_item_addons', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('transaction_item_variations', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
