<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\QrisService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /**
     * Display the POS interface.
     */
    public function index()
    {
        // Load active shift
        $activeShift = \App\Models\CashRegister::where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->route('pos.shift.index')->with('info', 'Silakan buka shift terlebih dahulu untuk mengakses POS.');
        }

        $categories = Category::orderBy('name')->get();
        $products = Product::with(['category', 'rawMaterials'])->where('is_active', true)->latest()->get();
        $rawMaterials = \App\Models\RawMaterial::all();

        return view('pos.index', compact('categories', 'products', 'activeShift', 'rawMaterials'));
    }

    /**
     * Generate Dynamic QRIS for the transaction.
     */
    public function generateQris(Request $request, QrisService $qrisService)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
        ]);

        try {
            $qrisString = $qrisService->generateDynamicQris((float) $request->amount);
            
            return response()->json([
                'success' => true,
                'qris_string' => $qrisString,
                'amount' => $request->amount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
