<?php

namespace App\Http\Controllers;

use App\Models\VariationGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VariationController extends Controller
{
    /**
     * Display a listing of the variation groups.
     */
    public function index(Request $request)
    {
        $query = VariationGroup::with('options');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $variationGroups = $query->latest()->paginate(10)->withQueryString();

        return view('variations.index', compact('variationGroups'));
    }

    /**
     * Store a newly created variation group in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'is_required' => 'boolean',
            'options' => 'required|array|min:1',
            'options.*.name' => 'required|string|max:255',
            'options.*.short_name' => 'nullable|string|max:50',
            'options.*.price_modifier' => 'nullable|numeric|min:0',
            'options.*.is_default' => 'boolean',
        ]);

        DB::transaction(function () use ($request) {
            $group = VariationGroup::create([
                'name' => $request->name,
                'type' => $request->type,
                'is_required' => $request->boolean('is_required'),
            ]);

            foreach ($request->options as $index => $opt) {
                // For single type, only one option can be default. We handle this below.
                $group->options()->create([
                    'name' => $opt['name'],
                    'short_name' => $opt['short_name'] ?? null,
                    'price_modifier' => $opt['price_modifier'] ?? 0,
                    'is_default' => isset($opt['is_default']) && $opt['is_default'] == 1,
                    'sort_order' => $index,
                ]);
            }

            // Enforce single default for 'single' type group
            if ($group->type === 'single') {
                $defaults = $group->options()->where('is_default', true)->get();
                if ($defaults->count() > 1) {
                    // Keep only the first one as default
                    $first = true;
                    foreach ($defaults as $opt) {
                        if ($first) {
                            $first = false;
                            continue;
                        }
                        $opt->update(['is_default' => false]);
                    }
                }
            }
        });

        return redirect()->route('variations.index')->with('success', 'Grup Variasi berhasil ditambahkan.');
    }

    /**
     * Update the specified variation group in storage.
     */
    public function update(Request $request, VariationGroup $variation)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:single,multiple',
            'is_required' => 'boolean',
            'options' => 'required|array|min:1',
            'options.*.id' => 'nullable|exists:variation_options,id',
            'options.*.name' => 'required|string|max:255',
            'options.*.short_name' => 'nullable|string|max:50',
            'options.*.price_modifier' => 'nullable|numeric|min:0',
            'options.*.is_default' => 'boolean',
        ]);

        DB::transaction(function () use ($request, $variation) {
            $variation->update([
                'name' => $request->name,
                'type' => $request->type,
                'is_required' => $request->boolean('is_required'),
            ]);

            $existingIds = [];

            foreach ($request->options as $index => $opt) {
                $isDefault = isset($opt['is_default']) && $opt['is_default'] == 1;

                if (!empty($opt['id'])) {
                    // Update existing
                    $option = $variation->options()->find($opt['id']);
                    if ($option) {
                        $option->update([
                            'name' => $opt['name'],
                            'short_name' => $opt['short_name'] ?? null,
                            'price_modifier' => $opt['price_modifier'] ?? 0,
                            'is_default' => $isDefault,
                            'sort_order' => $index,
                        ]);
                        $existingIds[] = $option->id;
                    }
                } else {
                    // Create new
                    $newOption = $variation->options()->create([
                        'name' => $opt['name'],
                        'short_name' => $opt['short_name'] ?? null,
                        'price_modifier' => $opt['price_modifier'] ?? 0,
                        'is_default' => $isDefault,
                        'sort_order' => $index,
                    ]);
                    $existingIds[] = $newOption->id;
                }
            }

            // Remove deleted options
            $variation->options()->whereNotIn('id', $existingIds)->delete();

            // Enforce single default for 'single' type group
            if ($variation->type === 'single') {
                $defaults = $variation->options()->where('is_default', true)->get();
                if ($defaults->count() > 1) {
                    $first = true;
                    foreach ($defaults as $opt) {
                        if ($first) {
                            $first = false;
                            continue;
                        }
                        $opt->update(['is_default' => false]);
                    }
                }
            }
        });

        return redirect()->route('variations.index')->with('success', 'Grup Variasi berhasil diperbarui.');
    }

    /**
     * Remove the specified variation group from storage.
     */
    public function destroy(VariationGroup $variation)
    {
        $variation->delete(); // Cascades options
        return redirect()->route('variations.index')->with('success', 'Grup Variasi berhasil dihapus.');
    }
}
