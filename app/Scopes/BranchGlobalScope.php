<?php

namespace App\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BranchGlobalScope implements Scope
{
    /**
     * Apply the branch scope to all queries on models using BranchScope trait.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $branchId = session('current_branch_id');

        if ($branchId) {
            $builder->where($model->getTable() . '.branch_id', $branchId);
        }
    }
}
