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
            if (!Schema::hasColumn('transactions', 'voucher_id')) {
                $table->foreignId('voucher_id')->nullable()->after('subtotal')->constrained('vouchers')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'voucher_discount_amount')) {
                $table->decimal('voucher_discount_amount', 12, 2)->default(0)->after('voucher_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['voucher_id']);
            $table->dropColumn(['voucher_id', 'voucher_discount_amount']);
        });
    }
};
