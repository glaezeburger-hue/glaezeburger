<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VariationGroup extends Model
{
    protected $fillable = [
        'name',
        'type',
        'is_required'
    ];

    protected $casts = [
        'is_required' => 'boolean'
    ];

    public function options(): HasMany
    {
        return $this->hasMany(VariationOption::class)->orderBy('sort_order', 'asc');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_variation_group')
                    ->withPivot('sort_order')
                    ->withTimestamps();
    }
}
