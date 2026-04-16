<?php

namespace App\Http\Controllers;

use App\Models\Addon;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddonController extends Controller
{
    /**
     * Display a listing of the addon.
     */
    public function index(Request $request)
    {
        $query = Addon::with('rawMaterials');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $addons = $query->latest()->paginate(10)->withQueryString();
        $rawMaterials = RawMaterial::orderBy('name')->get();

        return view('addons.index', compact('addons', 'rawMaterials'));
    }

    /**
     * Store a newly created addon in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.id' => 'required_with:ingredients|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $addon = Addon::create([
                'name' => $request->name,
                'selling_price' => $request->selling_price,
                'is_active' => $request->boolean('is_active', true),
                'cost_price' => 0, // Will be calculated after syncing ingredients
            ]);

            if ($request->has('ingredients')) {
                $syncData = [];
                foreach ($request->ingredients as $ingredient) {
                    if (!empty($ingredient['id']) && !empty($ingredient['quantity'])) {
                        $syncData[$ingredient['id']] = ['quantity' => $ingredient['quantity']];
                    }
                }
                $addon->rawMaterials()->sync($syncData);

                // Calculate cost price
                $addon->load('rawMaterials');
                $addon->update(['cost_price' => $addon->calculateCostPrice()]);
            }
        });

        return redirect()->route('addons.index')->with('success', 'Add-on berhasil ditambahkan.');
    }

    /**
     * Update the specified addon in storage.
     */
    public function update(Request $request, Addon $addon)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'selling_price' => 'required|numeric|min:0',
            'is_active' => 'boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.id' => 'required_with:ingredients|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $addon) {
            $addon->update([
                'name' => $request->name,
                'selling_price' => $request->selling_price,
                'is_active' => $request->boolean('is_active', true)
            ]);

            if ($request->has('ingredients')) {
                $syncData = [];
                foreach ($request->ingredients as $ingredient) {
                    if (!empty($ingredient['id']) && !empty($ingredient['quantity'])) {
                        $syncData[$ingredient['id']] = ['quantity' => $ingredient['quantity']];
                    }
                }
                $addon->rawMaterials()->sync($syncData);
                
                // Recalculate cost price
                $addon->load('rawMaterials');
                $addon->update(['cost_price' => $addon->calculateCostPrice()]);
            } else {
                $addon->rawMaterials()->sync([]);
                $addon->update(['cost_price' => 0]);
            }
        });

        return redirect()->route('addons.index')->with('success', 'Add-on berhasil diperbarui.');
    }

    /**
     * Remove the specified addon from storage.
     */
    public function destroy(Addon $addon)
    {
        $addon->delete();
        return redirect()->route('addons.index')->with('success', 'Add-on berhasil dihapus.');
    }

    /**
     * Sync cost_price for all addons based on current raw material prices.
     */
    public function syncAllCosts()
    {
        $addons = Addon::with('rawMaterials')->get();
        
        DB::transaction(function () use ($addons) {
            foreach ($addons as $addon) {
                if ($addon->rawMaterials->isNotEmpty()) {
                    $addon->update(['cost_price' => $addon->calculateCostPrice()]);
                }
            }
        });

        return back()->with('success', 'HPP untuk semua add-ons berhasil disinkronisasi dengan harga bahan baku terbaru.');
    }
}
