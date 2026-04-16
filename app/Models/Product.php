<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'sku',
        'description',
        'cost_price',
        'selling_price',
        'stock',
        'image_path',
        'is_active',
        'is_recipe_based'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_recipe_based' => 'boolean',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
    ];

    protected $appends = ['calculated_stock', 'stock_status'];

    protected static function booted()
    {
        static::creating(function ($product) {
            if (empty($product->slug)) {
                $product->slug = Str::slug($product->name);
            }
        });

        static::updating(function ($product) {
            if ($product->isDirty('name') && !$product->isDirty('slug')) {
                $product->slug = Str::slug($product->name);
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the stock status label.
     */
    public function getStockStatusAttribute()
    {
        $currentStock = $this->calculated_stock;
        
        if ($currentStock <= 0) {
            return 'Out of Stock';
        } elseif ($currentStock <= 10) {
            return 'Low Stock';
        }
        return 'In Stock';
    }

    /**
     * Calculate the dynamic stock for recipe-based products.
     */
    public function getCalculatedStockAttribute()
    {
        if (!$this->is_recipe_based) {
            return $this->stock;
        }

        if ($this->rawMaterials->isEmpty()) {
            return 0;
        }

        $possibleQuantities = [];
        foreach ($this->rawMaterials as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity;
            if ($requiredQuantity > 0) {
                // Calculate how many times the required quantity fits into the available stock
                // use floor directly on numeric values
                $possibleQuantities[] = floor($ingredient->stock / $requiredQuantity);
            } else {
                $possibleQuantities[] = 0;
            }
        }

        return !empty($possibleQuantities) ? min($possibleQuantities) : 0;
    }

    /**
     * The raw materials (ingredients) that make up this product.
     */
    public function rawMaterials()
    {
        return $this->belongsToMany(RawMaterial::class, 'product_ingredients')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Calculate HPP (Harga Pokok Penjualan) from recipe ingredients.
     * Sum of (pivot.quantity × raw_material.cost_per_unit)
     *
     * @return float
     */
    public function calculateHpp(): float
    {
        if (!$this->is_recipe_based || $this->rawMaterials->isEmpty()) {
            return (float) $this->cost_price;
        }

        return $this->rawMaterials->sum(function ($material) {
            return $material->pivot->quantity * $material->cost_per_unit;
        });
    }

    /**
     * Get Gross Profit Margin percentage.
     * Formula: ((selling_price - HPP) / selling_price) × 100
     *
     * @return float|null
     */
    public function getGrossMarginAttribute(): ?float
    {
        if ($this->selling_price <= 0) {
            return null;
        }

        $hpp = $this->calculateHpp();

        return round((($this->selling_price - $hpp) / $this->selling_price) * 100, 1);
    }

    /**
     * Relationship: A product can have many variation groups.
     */
    public function variationGroups()
    {
        return $this->belongsToMany(VariationGroup::class, 'product_variation_group')
                    ->withPivot('sort_order')
                    ->orderByPivot('sort_order', 'asc')
                    ->withTimestamps();
    }

    /**
     * Relationship: A product can have many addons.
     */
    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'product_addon')
                    ->withPivot('sort_order')
                    ->orderByPivot('sort_order', 'asc')
                    ->withTimestamps();
    }
}
