<?php
// Check which products have cost_price = 0
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TransactionItem;
use App\Models\Product;

// Find transaction items with cost_price = 0 and check their product
$zeroItems = TransactionItem::withoutGlobalScopes()
    ->where('cost_price', 0)
    ->select('id', 'product_id')
    ->get();

$productIds = $zeroItems->pluck('product_id')->unique();

echo "=== Products linked to zero-cost items ===" . PHP_EOL;
foreach ($productIds as $pid) {
    $product = Product::withoutGlobalScopes()->withTrashed()->find($pid);
    if ($product) {
        echo "  Product ID:{$product->id} | Name:{$product->name} | cost_price:{$product->cost_price} | deleted:" . ($product->trashed() ? 'YES' : 'NO') . PHP_EOL;
    } else {
        echo "  Product ID:{$pid} | NOT FOUND (hard deleted)" . PHP_EOL;
    }
}
echo PHP_EOL . "Count of zero-cost items: " . $zeroItems->count() . PHP_EOL;
