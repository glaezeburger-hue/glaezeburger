<?php

namespace App\Http\Controllers;

use App\Models\Branch;
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
        if (!session('current_branch_id')) {
            return redirect()->route('dashboard')->with('error', 'Akses Ditolak: Anda harus memilih cabang spesifik di menu atas untuk menggunakan mesin POS, bukan "Semua Cabang".');
        }

        // Load active shift (auto-scoped to current branch via BranchScope trait)
        $activeShift = \App\Models\CashRegister::where('status', 'open')
            ->first();

        if (!$activeShift) {
            return redirect()->route('pos.shift.index')->with('info', 'Silakan buka shift terlebih dahulu untuk mengakses POS.');
        }

        $categories = Category::orderBy('name')->get();
        $products = Product::with([
            'category', 
            'rawMaterials', 
            'variationGroups.options' => function($q) {
                $q->where('is_active', true)->with('excludedIngredients');
            },
            'addons' => function($q) {
                $q->where('is_active', true)->with('rawMaterials');
            }
        ])->where('is_active', true)->latest()->get();

        // Raw materials are auto-scoped to current branch
        $rawMaterials = \App\Models\RawMaterial::all();

        // Branch info for receipt customization
        $currentBranch = Branch::find(session('current_branch_id'));

        return view('pos.index', compact('categories', 'products', 'activeShift', 'rawMaterials', 'currentBranch'));
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

    /**
     * Return fresh product & raw material data as JSON (for soft-reset without page reload).
     */
    public function refreshStock()
    {
        $products = Product::with([
            'category', 
            'rawMaterials', 
            'variationGroups.options' => function($q) {
                $q->where('is_active', true);
            },
            'addons' => function($q) {
                $q->where('is_active', true);
            }
        ])->where('is_active', true)->latest()->get();

        // Auto-scoped to branch via trait
        $rawMaterials = \App\Models\RawMaterial::all();

        return response()->json([
            'products' => $products,
            'rawMaterials' => $rawMaterials,
        ]);
    }
}

