<?php

namespace App\Traits;

use App\Scopes\BranchGlobalScope;

/**
 * BranchScope Trait
 * 
 * Apply this trait to any model that should be automatically scoped
 * to the current branch. It adds a global scope that filters queries
 * by branch_id and auto-fills branch_id on creation.
 * 
 * Models using this: Transaction, TransactionItem, TransactionItemVariation, TransactionItemAddon, CashRegister, RawMaterial, Expense, Wastage
 */
trait BranchScope
{
    protected static function bootBranchScope(): void
    {
        static::addGlobalScope(new BranchGlobalScope);

        // Auto-fill branch_id on creating if not already set
        static::creating(function ($model) {
            if (!$model->branch_id) {
                $branchId = session('current_branch_id');
                if (!$branchId) {
                    throw new \Exception("Data tidak dapat disimpan: Harap pilih cabang spesifik (bukan 'Semua Cabang') sebelum melakukan penambahan data operasional.");
                }
                $model->branch_id = $branchId;
            }
        });
    }

    /**
     * Get the branch that owns this model.
     */
    public function branch()
    {
        return $this->belongsTo(\App\Models\Branch::class);
    }

    /**
     * Scope query to a specific branch, bypassing the global scope.
     */
    public function scopeForBranch($query, int $branchId)
    {
        return $query->withoutGlobalScope(BranchGlobalScope::class)
                     ->where($this->getTable() . '.branch_id', $branchId);
    }

    /**
     * Scope query to ALL branches (removes the global scope).
     */
    public function scopeAllBranches($query)
    {
        return $query->withoutGlobalScope(BranchGlobalScope::class);
    }
}
