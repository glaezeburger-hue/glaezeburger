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
        'cost_price',
        'quantity',
        'subtotal',
        'notes'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Calculate the total COGS for this transaction item
     * using the snapshotted cost_price, variation cost_modifiers, and addon cost_prices.
     *
     * IMPORTANT: Ensure variations and addons are eager-loaded before calling this method
     * to avoid N+1 query issues. Use: ->with(['variations', 'addons'])
     *
     * @return float
     */
    public function getItemCogs(): float
    {
        $baseCost = (float) $this->cost_price;

        // Variation cost modifiers (already snapshotted in transaction_item_variations)
        $variationCostModifier = $this->relationLoaded('variations') || $this->variations
            ? $this->variations->sum('cost_modifier')
            : 0;

        $adjustedCost = max(0, $baseCost + $variationCostModifier);
        $productCost = $this->quantity * $adjustedCost;

        // Addon costs (already snapshotted in transaction_item_addons)
        $addonCost = $this->relationLoaded('addons') || $this->addons
            ? $this->addons->sum(function ($addon) {
                return $addon->quantity * $this->quantity * (float) $addon->cost_price;
            })
            : 0;

        return $productCost + $addonCost;
    }

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
