<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add branch_id to all branch-scoped tables.
     * Strategy: add nullable → seed default branch → backfill → set non-null + FK.
     */
    public function up(): void
    {
        // ── Step 0: Update role enum to allow super_owner ──
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('owner', 'cashier', 'kitchen', 'super_owner') NOT NULL DEFAULT 'cashier'");

        // ── Step 1: Add nullable branch_id column to all affected tables ──
        $tables = ['users', 'transactions', 'cash_registers', 'raw_materials', 'expenses', 'wastages'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                if (!Schema::hasColumn($table, 'branch_id')) {
                    $t->unsignedBigInteger('branch_id')->nullable()->after('id');
                }
            });
        }

        // ── Step 2: Insert default branch (Centra Niaga Square — existing location) ──
        DB::table('branches')->insertOrIgnore([
            'id'                => 1,
            'name'              => 'Centra Niaga Square',
            'code'              => 'CNS',
            'address'           => 'Centra Niaga Square',
            'city'              => 'Cikarang Utara, Kab Bekasi',
            'phone'             => null,
            'receipt_header'    => 'Street Smash Burger',
            'receipt_footer'    => 'Follow & Tag Us',
            'receipt_instagram' => '@glaezeburger',
            'receipt_tiktok'    => '@glaezeburger',
            'is_active'         => true,
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);

        // ── Step 3: Backfill all existing data to branch_id = 1 ──
        foreach ($tables as $table) {
            DB::table($table)->whereNull('branch_id')->update(['branch_id' => 1]);
        }

        // ── Step 4: Set NOT NULL constraint + foreign key ──
        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                // Change column to NOT NULL
                DB::statement("ALTER TABLE {$table} MODIFY COLUMN branch_id BIGINT UNSIGNED NOT NULL");
                
                // Add foreign key only if it doesn't exist
                $fkName = $table === 'cash_registers' ? 'cash_registers_branch_id_foreign' : "{$table}_branch_id_foreign";
                $hasFk = collect(DB::select("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table}' AND CONSTRAINT_NAME = '{$fkName}'"))->isNotEmpty();
                
                if (!$hasFk) {
                    $t->foreign('branch_id', $fkName)
                        ->references('id')
                        ->on('branches')
                        ->onDelete('restrict');
                }
                
                // Add index only if it doesn't exist
                $idxName = $table === 'cash_registers' ? 'cash_registers_branch_id_index' : "{$table}_branch_id_index";
                $hasIdx = collect(DB::select("SHOW INDEX FROM {$table} WHERE Key_name = '{$idxName}'"))->isNotEmpty();
                
                if (!$hasIdx) {
                    $t->index('branch_id', $idxName);
                }
            });
        }

        // ── Step 5: Upgrade the primary owner account to super_owner ──
        DB::table('users')
            ->where('role', 'owner')
            ->orderBy('id')
            ->limit(1)
            ->update(['role' => 'super_owner']);
    }

    public function down(): void
    {
        // Revert super_owner back to owner
        DB::table('users')
            ->where('role', 'super_owner')
            ->update(['role' => 'owner']);

        $tables = ['users', 'transactions', 'cash_registers', 'raw_materials', 'expenses', 'wastages'];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $t) use ($table) {
                $t->dropForeign([$table === 'cash_registers' ? 'cash_registers_branch_id_foreign' : "{$table}_branch_id_foreign"]);
                $t->dropIndex(["{$table}_branch_id_index"]);
                $t->dropColumn('branch_id');
            });
        }

        // Remove default branch
        DB::table('branches')->where('id', 1)->delete();
    }
};
