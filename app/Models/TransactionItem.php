<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BranchScope;

class TransactionItem extends Model
{
    use HasFactory, BranchScope;

    protected $fillable = [
        'transaction_id',
        'branch_id',
        'product_id',
        'price',
        'quantity',
        'subtotal',
        'notes'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class)->withoutGlobalScopes();
    }

    /**
     * Get the historical variations selected for this transaction item.
     */
    public function variations()
    {
        return $this->hasMany(TransactionItemVariation::class);
    }

    /**
     * Get the historical addons selected for this transaction item.
     */
    public function addons()
    {
        return $this->hasMany(TransactionItemAddon::class);
    }
}
