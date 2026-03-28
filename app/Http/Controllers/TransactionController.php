<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    /**
     * Display the order history page.
     */
    public function index(Request $request)
    {
        $query = Transaction::with(['items.product', 'user', 'voucher']);

        // Search by invoice number
        if ($request->filled('search')) {
            $query->where('invoice_number', 'like', '%' . $request->search . '%');
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by order status
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }

        // Filter by source (POS vs Imported)
        if ($request->filled('source')) {
            if ($request->source === 'POS') {
                $query->where('is_imported', false);
            } elseif ($request->source === 'Imported') {
                $query->where('is_imported', true);
            }
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Date preset shortcuts
        if ($request->filled('period')) {
            $now = now();
            match ($request->period) {
                'today' => $query->whereDate('created_at', $now->toDateString()),
                '7days' => $query->whereDate('created_at', '>=', $now->subDays(7)->toDateString()),
                '30days' => $query->whereDate('created_at', '>=', $now->subDays(30)->toDateString()),
                default => null,
            };
        }

        // Clone query for summary before pagination
        $summaryQuery = clone $query;
        $summaryRevenue = $summaryQuery->sum('total_amount');
        
        $summaryQuery2 = clone $query;
        $summaryCount = $summaryQuery2->count();
        
        $summaryAvg = $summaryCount > 0 ? $summaryRevenue / $summaryCount : 0;

        $transactions = $query->latest()->paginate(15)->withQueryString();

        return view('transactions.index', compact(
            'transactions',
            'summaryRevenue',
            'summaryCount',
            'summaryAvg'
        ));
    }

    /**
     * Display the details of a single transaction (JSON for modal).
     */
    public function show(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user', 'voucher']);

        return response()->json([
            'id' => $transaction->id,
            'invoice_number' => $transaction->invoice_number,
            'created_at' => $transaction->created_at->format('d M Y, H:i'),
            'cashier' => $transaction->user->name ?? '-',
            'payment_method' => $transaction->payment_method,
            'order_status' => $transaction->order_status,
            'is_imported' => $transaction->is_imported,
            'subtotal' => $transaction->subtotal,
            'discount_type' => $transaction->discount_type,
            'discount_value' => $transaction->discount_value,
            'discount_amount' => $transaction->discount_amount,
            'voucher_code' => $transaction->voucher->code ?? null,
            'voucher_discount_amount' => $transaction->voucher_discount_amount,
            'net_sales' => $transaction->net_sales,
            'tax_amount' => $transaction->tax_amount,
            'total_amount' => $transaction->total_amount,
            'items' => $transaction->items->map(fn($item) => [
                'product_name' => $item->product->name ?? 'Deleted Product',
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->subtotal,
                'notes' => $item->notes,
                'variations' => $item->variations->map(fn($var) => [
                    'group' => $var->variation_name,
                    'option' => $var->option_name
                ]),
            ]),
        ]);
    }

    /**
     * Update the order status (e.g., mark as completed).
     */
    public function updateStatus(Transaction $transaction)
    {
        $transaction->update(['order_status' => 'Sudah']);
        return back()->with('success', "Order {$transaction->invoice_number} marked as Completed.");
    }

    /**
     * Confirm QRIS payment via AJAX.
     */
    public function confirmPayment(Request $request, Transaction $transaction)
    {
        $data = ['payment_status' => 'Paid'];
        
        if ($request->has('payment_reference')) {
            $data['payment_reference'] = $request->payment_reference;
        }

        $transaction->update($data);

        return response()->json([
            'success' => true,
            'message' => "Payment for {$transaction->invoice_number} confirmed."
        ]);
    }

    /**
     * Store a newly created transaction in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|in:Cash,QRIS',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.notes' => 'nullable|string|max:255',
            'cart.*.variations' => 'nullable|array',
            'apply_tax' => 'boolean',
            'discount_type' => 'nullable|in:percentage,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'voucher_code' => 'nullable|string',
            'transaction_date' => 'nullable|date',
        ]);

        // Check for active shift
        $activeShift = \App\Models\CashRegister::where('status', 'open')
            ->first();

        if (!$activeShift) {
            return response()->json([
                'success' => false,
                'message' => 'Shift belum dibuka! Silakan buka shift kasir terlebih dahulu.'
            ], 403);
        }

        return DB::transaction(function () use ($request, $activeShift) {
            // Maintain local consumption tracker for the whole cart
            $tempRawMaterialUsage = [];
            $tempProductUsage = [];
            $subtotal = 0;
            $items = [];

            // Custom Timestamp Handling
            $timestamp = $request->filled('transaction_date') 
                ? \Carbon\Carbon::parse($request->transaction_date) 
                : now();

            // Calculate totals and validate stock first
            foreach ($request->cart as $item) {
                $product = Product::with('rawMaterials')->lockForUpdate()->find($item['id']);
                
                // Calculate Variation Price Modifiers
                $variationSubtotal = 0;
                $selectedVariations = []; 
                if (isset($item['variations']) && is_array($item['variations'])) {
                    foreach ($item['variations'] as $groupId => $groupData) {
                        if (isset($groupData['selected']) && is_array($groupData['selected'])) {
                            foreach ($groupData['selected'] as $optionId) {
                                $option = \App\Models\VariationOption::find($optionId);
                                if ($option) {
                                    $variationSubtotal += $option->price_modifier;
                                    $group = \App\Models\VariationGroup::find($groupId);
                                    
                                    $selectedVariations[] = [
                                        'variation_option_id' => $optionId,
                                        'variation_name' => $group ? $group->name : '',
                                        'option_name' => $option->short_name ?: $option->name,
                                        'price_modifier' => $option->price_modifier,
                                        'created_at' => $timestamp,
                                        'updated_at' => $timestamp,
                                    ];
                                }
                            }
                        }
                    }
                }

                if ($product->is_recipe_based && $product->rawMaterials->isNotEmpty()) {
                    // Check each raw material has enough stock
                    foreach ($product->rawMaterials as $ingredient) {
                        $needed = $ingredient->pivot->quantity * $item['quantity'];
                        
                        // Track cumulative usage for this specific material
                        $tempRawMaterialUsage[$ingredient->id] = ($tempRawMaterialUsage[$ingredient->id] ?? 0) + $needed;
                        
                        $rawMaterial = \App\Models\RawMaterial::lockForUpdate()->find($ingredient->id);
                        if ($rawMaterial->stock < $tempRawMaterialUsage[$ingredient->id]) {
                            throw new \Exception("Ingredient {$rawMaterial->name} stock insufficient for the total order.");
                        }
                    }
                } else {
                    // Track cumulative usage for direct product stock
                    $tempProductUsage[$product->id] = ($tempProductUsage[$product->id] ?? 0) + $item['quantity'];
                    
                    if ($product->stock < $tempProductUsage[$product->id]) {
                        throw new \Exception("Stock for {$product->name} is insufficient.");
                    }
                }

                $finalPrice = $product->selling_price + $variationSubtotal;
                $itemSubtotal = $finalPrice * $item['quantity'];
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'price' => $finalPrice,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                    'product_model' => $product,
                    'variations' => $selectedVariations
                ];
            }

            // Calculate Discount
            $discountAmount = 0;
            $discountType = $request->input('discount_type');
            $discountValue = (float) $request->input('discount_value', 0); // Important cast

            if ($discountType === 'percentage') {
                $discountAmount = $subtotal * ($discountValue / 100);
            } elseif ($discountType === 'nominal') {
                $discountAmount = $discountValue;
            }

            // Cannot discount more than subtotal
            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }

            // Calculate Voucher Discount
            $voucherDiscountAmount = 0;
            $voucherId = null;
            $voucherModel = null;

            if ($request->filled('voucher_code')) {
                $vResult = \App\Http\Controllers\VoucherController::calculateVoucherDiscount(
                    $request->voucher_code,
                    $subtotal,
                    $request->cart
                );
                
                if (!$vResult['success']) {
                    throw new \Exception($vResult['message']);
                }

                $voucherDiscountAmount = $vResult['discount_amount'];
                $voucherModel = $vResult['voucher'];
                $voucherId = $voucherModel->id;
            }

            // Cap combined discounts
            $totalDiscounts = $discountAmount + $voucherDiscountAmount;
            if ($totalDiscounts > $subtotal) {
                // Prioritize voucher, reduce manual discount if needed
                $discountAmount = max(0, $subtotal - $voucherDiscountAmount);
                $totalDiscounts = $subtotal;
            }

            // Calculate Netsales & Tax
            $netSales = max(0, $subtotal - $totalDiscounts);
            $taxAmount = $request->boolean('apply_tax') ? $netSales * 0.10 : 0;
            $totalAmount = $netSales + $taxAmount;

            // 1. Create Transaction
            $transaction = new Transaction([
                'invoice_number' => Transaction::generateInvoiceNumber(),
                'user_id' => auth()->id(),
                'cash_register_id' => $activeShift->id,
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'net_sales' => $netSales,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'voucher_id' => $voucherId,
                'voucher_discount_amount' => $voucherDiscountAmount,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_method === 'QRIS' ? 'Pending' : 'Paid',
                'order_status' => 'Belum', // Set as 'Belum' initially so it shows up in KDS
            ]);
            $transaction->created_at = $timestamp;
            $transaction->updated_at = $timestamp;
            $transaction->save();

            // 2. Create Transaction Items and Deduct Stock
            foreach ($items as $itemData) {
                $product = $itemData['product_model'];
                
                $tItem = new \App\Models\TransactionItem([
                    'product_id' => $itemData['product_id'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['subtotal'],
                    'notes' => $itemData['notes'],
                ]);
                $tItem->transaction_id = $transaction->id;
                $tItem->created_at = $timestamp;
                $tItem->updated_at = $timestamp;
                $tItem->save();

                if (!empty($itemData['variations'])) {
                    $varsToInsert = array_map(function($var) use ($tItem) {
                        $var['transaction_item_id'] = $tItem->id;
                        return $var;
                    }, $itemData['variations']);
                    
                    \App\Models\TransactionItemVariation::insert($varsToInsert);
                }

                // Deduct stock
                if ($product->is_recipe_based && $product->rawMaterials->isNotEmpty()) {
                    foreach ($product->rawMaterials as $ingredient) {
                        \App\Models\RawMaterial::where('id', $ingredient->id)
                            ->decrement('stock', $ingredient->pivot->quantity * $itemData['quantity']);
                    }
                } else {
                    $product->decrement('stock', $itemData['quantity']);
                }
            }

            // 3. Increment Voucher usage if any
            if ($voucherModel) {
                $voucherModel->increment('used_count');
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaction completed successfully.',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'invoice_number' => $transaction->invoice_number,
                    'total_amount' => $transaction->total_amount,
                    'payment_method' => $transaction->payment_method,
                ]
            ]);
        });
    }

    /**
     * Display the thermal receipt for a transaction.
     */
    public function showReceipt(Transaction $transaction)
    {
        $transaction->load(['items.product', 'items.variations', 'user']);
        return view('pos.receipt', compact('transaction'));
    }

    /**
     * Return receipt data directly as JSON for client-side direct printing (ESC/POS).
     */
    public function receiptData(Transaction $transaction)
    {
        $transaction->load(['items.product', 'user', 'voucher']);

        return response()->json([
            'id' => $transaction->id,
            'invoice_number' => $transaction->invoice_number,
            'created_at' => $transaction->created_at->format('d/m/Y H:i'),
            'cashier' => $transaction->user->name ?? 'N/A',
            'subtotal' => $transaction->subtotal,
            'discount_amount' => $transaction->discount_amount,
            'voucher_code' => $transaction->voucher->code ?? null,
            'voucher_discount_amount' => $transaction->voucher_discount_amount,
            'tax_amount' => $transaction->tax_amount,
            'total_amount' => $transaction->total_amount,
            'payment_method' => $transaction->payment_method,
            'items' => $transaction->items->map(function ($item) {
                return [
                    'product_name' => $item->product->name ?? 'Deleted Product',
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'subtotal' => $item->subtotal,
                    'notes' => $item->notes,
                    'variations' => $item->variations->map(fn($var) => [
                        'group' => $var->variation_name,
                        'option' => $var->option_name,
                        'price_modifier' => $var->price_modifier
                    ])->toArray(),
                ];
            })
        ]);
    }

    /**
     * Display the form to import historical transactions.
     */
    public function importForm()
    {
        $products = Product::orderBy('name')->get();
        return view('transactions.import', compact('products'));
    }

    /**
     * Store a manually imported historical transaction.
     */
    public function importStore(Request $request)
    {
        $request->validate([
            'transaction_date' => 'required|date|before_or_equal:today',
            'payment_method' => 'required|in:Cash,QRIS',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|exists:products,id',
            'cart.*.quantity' => 'required|integer|min:1',
            'cart.*.custom_price' => 'required|numeric|min:0',
            'cart.*.notes' => 'nullable|string|max:255',
            'apply_tax' => 'boolean',
            'discount_type' => 'nullable|in:percentage,nominal',
            'discount_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000'
        ]);

        return DB::transaction(function () use ($request) {
            $subtotal = 0;
            $items = [];

            // Calculate totals (NO stock validation for historical imports)
            foreach ($request->cart as $item) {
                $product = Product::find($item['id']);
                $price = (float) $item['custom_price']; // Use custom price
                $itemSubtotal = $price * $item['quantity'];
                
                $subtotal += $itemSubtotal;

                $items[] = [
                    'product_id' => $product->id,
                    'price' => $price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $itemSubtotal,
                    'notes' => $item['notes'] ?? null,
                ];
            }

            // Calculate Discount
            $discountAmount = 0;
            $discountType = $request->input('discount_type');
            $discountValue = (float) $request->input('discount_value', 0);

            if ($discountType === 'percentage') {
                $discountAmount = $subtotal * ($discountValue / 100);
            } elseif ($discountType === 'nominal') {
                $discountAmount = $discountValue;
            }

            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }

            // Calculate Netsales & Tax
            $netSales = max(0, $subtotal - $discountAmount);
            $taxAmount = $request->boolean('apply_tax') ? $netSales * 0.10 : 0;
            $totalAmount = $netSales + $taxAmount;

            $timestamp = \Carbon\Carbon::parse($request->transaction_date)->setTimeFromTimeString(now()->toTimeString());

            // 1. Generate Invoice Number based on transaction_date (for backdated consistency)
            $dateStr = $timestamp->format('Ymd');
            $lastTxn = Transaction::whereDate('created_at', $timestamp->toDateString())->latest()->first();
            $seq = $lastTxn ? (int)substr($lastTxn->invoice_number, -4) + 1 : 1;
            $invoiceNumber = 'INV-' . $dateStr . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);

            // 1. Create Transaction
            $transaction = new Transaction([
                'invoice_number' => $invoiceNumber,
                'user_id' => auth()->id(),
                'subtotal' => $subtotal,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'net_sales' => $netSales,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $request->payment_method,
                'order_status' => 'Sudah', // Immediately marked as done
                'is_imported' => true // Audit trail flag
            ]);
            $transaction->created_at = $timestamp;
            $transaction->updated_at = $timestamp;
            $transaction->save();

            // 2. Create Transaction Items (NO stock deduction)
            foreach ($items as $itemData) {
                $tItem = new \App\Models\TransactionItem([
                    'product_id' => $itemData['product_id'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'subtotal' => $itemData['subtotal'],
                    'notes' => $itemData['notes'],
                ]);
                $tItem->transaction_id = $transaction->id;
                $tItem->created_at = $timestamp;
                $tItem->updated_at = $timestamp;
                $tItem->save();
            }

            return response()->json([
                'success' => true,
                'message' => 'Historical transaction imported successfully.',
                'invoice_number' => $transaction->invoice_number,
                'transaction_id' => $transaction->id
            ]);
        });
    }

    /**
     * Delete a transaction and restore stocks/vouchers (Owner Only).
     */
    public function destroy(Transaction $transaction)
    {
        return DB::transaction(function () use ($transaction) {
            // 1. Restore Stock if not imported
            if (!$transaction->is_imported) {
                foreach ($transaction->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        if ($product->is_recipe_based && $product->rawMaterials->isNotEmpty()) {
                            foreach ($product->rawMaterials as $ingredient) {
                                \App\Models\RawMaterial::where('id', $ingredient->id)
                                    ->increment('stock', $ingredient->pivot->quantity * $item->quantity);
                            }
                        } else {
                            $product->increment('stock', $item->quantity);
                        }
                    }
                }
            }

            // 2. Restore Voucher usage if any
            if ($transaction->voucher_id) {
                $transaction->voucher()->decrement('used_count');
            }

            // 3. Delete items and transaction
            $transaction->items()->delete();
            $transaction->delete();

            return response()->json([
                'success' => true,
                'message' => "Transaction {$transaction->invoice_number} deleted and stock restored."
            ]);
        });
    }
}
