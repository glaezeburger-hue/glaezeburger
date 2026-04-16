<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transaction_item_variations', function (Blueprint $table) {
            $table->decimal('cost_modifier', 15, 2)->default(0)->after('price_modifier');
            $table->json('excluded_ingredient_ids')->nullable()->after('cost_modifier');
        });
    }

    public function down(): void
    {
        Schema::table('transaction_item_variations', function (Blueprint $table) {
            $table->dropColumn(['cost_modifier', 'excluded_ingredient_ids']);
        });
    }
};
