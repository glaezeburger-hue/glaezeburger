<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BranchScope;

class TransactionItemAddon extends Model
{
    use BranchScope;

    protected $fillable = [
        'transaction_item_id',
        'branch_id',
        'addon_id',
        'addon_name',
        'selling_price',
        'cost_price',
        'quantity'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'quantity' => 'integer'
    ];

    public function transactionItem()
    {
        return $this->belongsTo(TransactionItem::class);
    }

    public function addon()
    {
        return $this->belongsTo(Addon::class);
    }
}
