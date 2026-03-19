<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RawMaterial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'sku',
        'unit',
        'stock',
        'low_stock_threshold',
    ];

    /**
     * Get the products that use this raw material in their recipe.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients')
                    ->withPivot('quantity')
                    ->withTimestamps();
    }

    /**
     * Get the stock status label.
     */
    public function getStockStatusAttribute()
    {
        if ($this->stock <= 0) {
            return 'Out of Stock';
        } elseif ($this->stock <= $this->low_stock_threshold) {
            return 'Low Stock';
        }
        return 'In Stock';
    }
}
