<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'selling_price',
        'cost_price',
        'is_active',
        'sort_order'
    ];

    protected $casts = [
        'selling_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    /**
     * Get the raw materials (ingredients) that make up this addon.
     */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'addon_ingredients')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Get the products that support this addon.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_addon')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }

    /**
     * Calculate cost price (HPP) from ingredients.
     */
    public function calculateCostPrice(): float
    {
        if ($this->rawMaterials->isEmpty()) {
            return 0.0;
        }

        return $this->rawMaterials->sum(function ($material) {
            return $material->pivot->quantity * $material->cost_per_unit;
        });
    }

    /**
     * Get Gross Profit Margin percentage.
     */
    public function getGrossMarginAttribute(): ?float
    {
        if ($this->selling_price <= 0) {
            return null;
        }

        return round((($this->selling_price - $this->cost_price) / $this->selling_price) * 100, 1);
    }

    /**
     * Check if the addon's ingredients have enough stock for at least 1 portion.
     */
    public function getIsAvailableAttribute(): bool
    {
        if ($this->rawMaterials->isEmpty()) {
            return true; // No ingredients, always available
        }

        foreach ($this->rawMaterials as $material) {
            if ($material->stock < $material->pivot->quantity) {
                return false;
            }
        }

        return true;
    }
}
