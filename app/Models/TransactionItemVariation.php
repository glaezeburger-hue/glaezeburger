<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\BranchScope;

class TransactionItemVariation extends Model
{
    use BranchScope;

    protected $fillable = [
        'transaction_item_id',
        'branch_id',
        'variation_option_id',
        'variation_name',
        'option_name',
        'price_modifier',
        'cost_modifier',
        'excluded_ingredient_ids'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'cost_modifier' => 'decimal:2',
        'excluded_ingredient_ids' => 'array'
    ];

    public function transactionItem(): BelongsTo
    {
        return $this->belongsTo(TransactionItem::class);
    }
    
    public function variationOption(): BelongsTo
    {
        return $this->belongsTo(VariationOption::class);
    }
}
