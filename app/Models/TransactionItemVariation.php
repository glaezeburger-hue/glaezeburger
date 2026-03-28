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
        'price_modifier'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2'
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
