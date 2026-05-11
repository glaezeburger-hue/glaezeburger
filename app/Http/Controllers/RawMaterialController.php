<?php

namespace App\Http\Controllers;

use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RawMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Block data mutation if no specific branch is selected
            if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && !session('current_branch_id')) {
                return back()->with('error', 'Harap pilih cabang spesifik sebelum melakukan perubahan data.');
            }
            return $next($request);
        })->only(['store', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RawMaterial::query();

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        $rawMaterials = $query->latest()->paginate(10)->withQueryString();

        return view('raw-materials.index', compact('rawMaterials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'required', 
                'string', 
                Rule::unique('raw_materials')->where('branch_id', session('current_branch_id'))->whereNull('deleted_at')
            ],
            'unit' => 'required|string|max:50',
            'stock' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        try {
            RawMaterial::create($validated);
            return redirect()->route('raw-materials.index')->with('success', 'Raw material added successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RawMaterial $rawMaterial)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => [
                'required', 
                'string', 
                Rule::unique('raw_materials')
                    ->where('branch_id', session('current_branch_id'))
                    ->whereNull('deleted_at')
                    ->ignore($rawMaterial->id)
            ],
            'unit' => 'required|string|max:50',
            'stock' => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'cost_per_unit' => 'required|numeric|min:0',
        ]);

        $rawMaterial->update($validated);

        return redirect()->route('raw-materials.index')->with('success', 'Raw material updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RawMaterial $rawMaterial)
    {
        // Don't delete if it's currently used in any products?
        // Let's just do a soft delete for now. The DB is set up with soft deletes.
        $rawMaterial->delete();
        
        return redirect()->route('raw-materials.index')->with('success', 'Raw material deleted successfully.');
    }
}
