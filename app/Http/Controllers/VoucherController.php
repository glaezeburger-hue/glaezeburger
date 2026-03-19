<?php

namespace App\Http\Controllers;

use App\Models\Voucher;
use App\Models\Product;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $vouchers = Voucher::with('freeProduct')->latest()->paginate(10);
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('vouchers.index', compact('vouchers', 'products'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:vouchers,code|regex:/^[A-Z0-9]+$/',
            'reward_type' => 'required|in:percentage,nominal,free_item',
            'reward_value' => 'required_unless:reward_type,free_item|numeric|min:0',
            'free_product_id' => 'required_if:reward_type,free_item|nullable|exists:products,id',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean'
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['min_purchase'] = $validated['min_purchase'] ?? 0;
        
        if ($validated['reward_type'] === 'free_item') {
            $validated['reward_value'] = 0;
            $validated['max_discount'] = null;
        } else {
            $validated['free_product_id'] = null;
        }

        Voucher::create($validated);
        return redirect()->route('vouchers.index')->with('success', 'Voucher created successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Voucher $voucher)
    {
        $validated = $request->validate([
            'code' => 'required|string|regex:/^[A-Z0-9]+$/|unique:vouchers,code,' . $voucher->id,
            'reward_type' => 'required|in:percentage,nominal,free_item',
            'reward_value' => 'required_unless:reward_type,free_item|numeric|min:0',
            'free_product_id' => 'required_if:reward_type,free_item|nullable|exists:products,id',
            'min_purchase' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'quota' => 'nullable|integer|min:1',
            'valid_from' => 'nullable|date',
            'valid_until' => 'nullable|date|after_or_equal:valid_from',
            'is_active' => 'boolean'
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['min_purchase'] = $validated['min_purchase'] ?? 0;
        
        if ($validated['reward_type'] === 'free_item') {
            $validated['reward_value'] = 0;
            $validated['max_discount'] = null;
        } else {
            $validated['free_product_id'] = null;
        }

        $voucher->update($validated);
        return redirect()->route('vouchers.index')->with('success', 'Voucher updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();
        return redirect()->route('vouchers.index')->with('success', 'Voucher deleted successfully.');
    }

    /**
     * API: Validate voucher for POS
     */
    public function validateVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'cart_subtotal' => 'required|numeric|min:0',
            'cart_items' => 'required|array',
            'cart_items.*.id' => 'required|integer'
        ]);

        $result = self::calculateVoucherDiscount(
            $request->voucher_code, 
            $request->cart_subtotal, 
            $request->cart_items
        );

        if (!$result['success']) {
            return response()->json(['success' => false, 'message' => $result['message']], 400);
        }

        return response()->json([
            'success' => true,
            'discount_amount' => $result['discount_amount'],
            'voucher_code' => $result['voucher']->code
        ]);
    }

    /**
     * Shared logic for calculating voucher discount
     */
    public static function calculateVoucherDiscount($code, $subtotal, $cartItems)
    {
        $voucher = Voucher::with('freeProduct')->where('code', strtoupper($code))->first();

        if (!$voucher) {
            return ['success' => false, 'message' => 'Voucher not found.'];
        }

        if (!$voucher->is_active) {
            return ['success' => false, 'message' => 'Voucher is not active.'];
        }

        if ($voucher->valid_from && now()->lt($voucher->valid_from)) {
            return ['success' => false, 'message' => 'Voucher is not yet valid.'];
        }

        if ($voucher->valid_until && now()->gt($voucher->valid_until)) {
            return ['success' => false, 'message' => 'Voucher has expired.'];
        }

        if ($voucher->quota !== null && $voucher->used_count >= $voucher->quota) {
            return ['success' => false, 'message' => 'Voucher usage limits reached.'];
        }

        if ($subtotal < $voucher->min_purchase) {
            return ['success' => false, 'message' => 'Minimum purchase of Rp ' . number_format($voucher->min_purchase, 0, ',', '.') . ' required.'];
        }

        $discountAmount = 0;

        if ($voucher->reward_type === 'percentage') {
            $discountAmount = $subtotal * ($voucher->reward_value / 100);
            if ($voucher->max_discount !== null && $discountAmount > $voucher->max_discount) {
                $discountAmount = $voucher->max_discount;
            }
        } elseif ($voucher->reward_type === 'nominal') {
            $discountAmount = $voucher->reward_value;
        } elseif ($voucher->reward_type === 'free_item') {
            // Check if free item is in cart
            $productInCart = collect($cartItems)->firstWhere('id', $voucher->free_product_id);
            if (!$productInCart) {
                return ['success' => false, 'message' => "You must add the free item ({$voucher->freeProduct->name}) to your cart first to apply this code."];
            }
            $discountAmount = (float)$voucher->freeProduct->selling_price;
        }

        // Cap discount
        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }

        return [
            'success' => true, 
            'discount_amount' => $discountAmount,
            'voucher' => $voucher
        ];
    }
}
