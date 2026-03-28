<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\RawMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['category', 'rawMaterials', 'variationGroups']);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
        }

        if ($request->has('category_id') && $request->category_id != '') {
            $query->where('category_id', $request->category_id);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $rawMaterials = RawMaterial::orderBy('name')->get(['id', 'name', 'unit', 'cost_per_unit']);
        $variationGroups = \App\Models\VariationGroup::with('options')->get();

        return view('products.index', compact('products', 'categories', 'rawMaterials', 'variationGroups'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name',
            'sku' => 'required|string|unique:products,sku',
            'description' => 'nullable|string',
            'cost_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean',
            'is_recipe_based' => 'boolean',
            'ingredients' => 'nullable|array',
            'ingredients.*.id' => 'required_with:ingredients|exists:raw_materials,id',
            'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
            'variation_groups' => 'nullable|array',
            'variation_groups.*' => 'exists:variation_groups,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        // Handle recipe-based stock
        $validated['is_recipe_based'] = $request->input('is_recipe_based', 0) ? 1 : 0;
        if ($validated['is_recipe_based']) {
            $validated['stock'] = 0; // Or keep it null, based on your logic, but defaults to 0
        } else {
            $validated['stock'] = $request->input('stock', 0);
        }

        $product = Product::create($validated);
        
        if ($validated['is_recipe_based'] && $request->has('ingredients')) {
            $syncData = [];
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['id']) && !empty($ingredient['quantity'])) {
                    $syncData[$ingredient['id']] = ['quantity' => $ingredient['quantity']];
                }
            }
            $product->rawMaterials()->sync($syncData);

            // Auto-update cost_price from calculated HPP
            $product->load('rawMaterials');
            $product->update(['cost_price' => $product->calculateHpp()]);
        }

        if ($request->has('variation_groups')) {
            $syncData = [];
            foreach ($request->variation_groups as $index => $groupId) {
                $syncData[$groupId] = ['sort_order' => $index];
            }
            $product->variationGroups()->sync($syncData);
        }

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        // If they somehow land here, redirect back to index
        return redirect()->route('products.index');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        try {
            $validated = $request->validate([
                'category_id' => 'required|exists:categories,id',
                'name' => 'required|string|max:255|unique:products,name,' . $product->id,
                'sku' => 'required|string|unique:products,sku,' . $product->id,
                'description' => 'nullable|string',
                'cost_price' => 'required|numeric|min:0',
                'selling_price' => 'required|numeric|min:0',
                'stock' => 'nullable|integer|min:0',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'is_active' => 'boolean',
                'is_recipe_based' => 'boolean',
                'ingredients' => 'nullable|array',
                'ingredients.*.id' => 'required_with:ingredients|exists:raw_materials,id',
                'ingredients.*.quantity' => 'required_with:ingredients|numeric|min:0',
                'variation_groups' => 'nullable|array',
                'variation_groups.*' => 'exists:variation_groups,id',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            dd($e->errors(), $request->all());
        }

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('products', 'public');
        }

        // Handle recipe-based stock
        $validated['is_recipe_based'] = $request->input('is_recipe_based', 0) ? 1 : 0;
        if ($validated['is_recipe_based']) {
            $validated['stock'] = 0;
        } else {
            $validated['stock'] = $request->input('stock', 0);
        }

        $product->update($validated);

        if ($validated['is_recipe_based'] && $request->has('ingredients')) {
            $syncData = [];
            foreach ($request->ingredients as $ingredient) {
                if (!empty($ingredient['id']) && !empty($ingredient['quantity'])) {
                    $syncData[$ingredient['id']] = ['quantity' => $ingredient['quantity']];
                }
            }
            $product->rawMaterials()->sync($syncData);

            // Auto-update cost_price from calculated HPP
            $product->load('rawMaterials');
            $product->update(['cost_price' => $product->calculateHpp()]);
        } else {
            // Un-sync if it's no longer recipe-based
            $product->rawMaterials()->sync([]);
        }

        if ($request->has('variation_groups')) {
            $syncData = [];
            foreach ($request->variation_groups as $index => $groupId) {
                $syncData[$groupId] = ['sort_order' => $index];
            }
            $product->variationGroups()->sync($syncData);
        } else {
            $product->variationGroups()->sync([]);
        }

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    /**
     * Toggle the active status of the product.
     */
    public function toggleActive(Product $product)
    {
        $product->is_active = !$product->is_active;
        $product->save();

        return response()->json([
            'success' => true,
            'is_active' => $product->is_active,
            'message' => 'Status updated successfully.'
        ]);
    }
}
