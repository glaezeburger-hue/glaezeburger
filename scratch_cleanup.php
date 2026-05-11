<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['users', 'transactions', 'cash_registers', 'raw_materials', 'expenses', 'wastages'];
foreach ($tables as $table) {
    if (Schema::hasColumn($table, 'branch_id')) {
        Schema::table($table, function ($t) use ($table) {
            try { $t->dropForeign([$table . '_branch_id_foreign']); } catch (\Exception $e) {}
            try { $t->dropIndex([$table . '_branch_id_index']); } catch (\Exception $e) {}
            $t->dropColumn('branch_id');
            echo "Dropped branch_id from $table\n";
        });
    }
}
DB::table('branches')->where('id', 1)->delete();
echo "Deleted branch 1\n";
