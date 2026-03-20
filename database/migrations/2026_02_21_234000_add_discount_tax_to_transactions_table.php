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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->after('user_id')->default(0);
            }
            if (!Schema::hasColumn('transactions', 'discount_type')) {
                $table->enum('discount_type', ['percentage', 'nominal'])->nullable()->after('subtotal');
            }
            if (!Schema::hasColumn('transactions', 'discount_value')) {
                $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            }
            if (!Schema::hasColumn('transactions', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('discount_value');
            }
            if (!Schema::hasColumn('transactions', 'net_sales')) {
                $table->decimal('net_sales', 12, 2)->after('discount_amount')->default(0);
            }
            if (!Schema::hasColumn('transactions', 'tax_amount')) {
                $table->decimal('tax_amount', 12, 2)->default(0)->after('net_sales');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal',
                'discount_type',
                'discount_value',
                'discount_amount',
                'net_sales',
                'tax_amount'
            ]);
        });
    }
};
