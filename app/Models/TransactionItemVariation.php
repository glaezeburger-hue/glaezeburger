<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItemVariation extends Model
{
    protected $fillable = [
        'transaction_item_id',
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
