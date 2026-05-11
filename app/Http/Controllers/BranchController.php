<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    /**
     * Display branch management page.
     */
    public function index()
    {
        $branches = Branch::withCount(['users', 'transactions'])
            ->orderBy('name')
            ->get();

        return view('branches.index', compact('branches'));
    }

    /**
     * Store a newly created branch.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:10|unique:branches,code|alpha_num',
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'receipt_header'    => 'nullable|string|max:255',
            'receipt_footer'    => 'nullable|string|max:255',
            'receipt_instagram' => 'nullable|string|max:255',
            'receipt_tiktok'    => 'nullable|string|max:255',
        ]);

        // Ensure code is uppercase
        $validated['code'] = strtoupper($validated['code']);

        Branch::create($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Cabang baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified branch.
     */
    public function edit(Branch $branch)
    {
        return view('branches.edit', compact('branch'));
    }

    /**
     * Update the specified branch.
     */
    public function update(Request $request, Branch $branch)
    {
        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'code'              => 'required|string|max:10|alpha_num|unique:branches,code,' . $branch->id,
            'address'           => 'nullable|string|max:500',
            'city'              => 'nullable|string|max:255',
            'phone'             => 'nullable|string|max:20',
            'receipt_header'    => 'nullable|string|max:255',
            'receipt_footer'    => 'nullable|string|max:255',
            'receipt_instagram' => 'nullable|string|max:255',
            'receipt_tiktok'    => 'nullable|string|max:255',
        ]);

        $validated['code'] = strtoupper($validated['code']);

        $branch->update($validated);

        return redirect()->route('branches.index')
            ->with('success', 'Data cabang berhasil diperbarui.');
    }

    /**
     * Toggle active status of a branch.
     */
    public function toggleActive(Branch $branch)
    {
        // Don't allow deactivating the last active branch
        if ($branch->is_active && Branch::where('is_active', true)->count() <= 1) {
            return redirect()->route('branches.index')
                ->with('error', 'Tidak bisa menonaktifkan satu-satunya cabang aktif.');
        }

        $branch->update(['is_active' => !$branch->is_active]);

        $status = $branch->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('branches.index')
            ->with('success', "Cabang {$branch->name} berhasil {$status}.");
    }

    /**
     * Remove the specified branch (only if no data attached).
     */
    public function destroy(Branch $branch)
    {
        // Prevent deletion if branch has associated data
        if ($branch->transactions()->count() > 0) {
            return redirect()->route('branches.index')
                ->with('error', 'Cabang ini memiliki data transaksi. Gunakan fitur nonaktifkan sebagai gantinya.');
        }

        $branch->delete();

        return redirect()->route('branches.index')
            ->with('success', 'Cabang berhasil dihapus.');
    }
}
