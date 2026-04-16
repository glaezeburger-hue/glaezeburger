<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class VariationOption extends Model
{
    protected $fillable = [
        'variation_group_id',
        'name',
        'short_name',
        'price_modifier',
        'cost_modifier',
        'is_default',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'cost_modifier' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(VariationGroup::class, 'variation_group_id');
    }

    public function excludedIngredients(): BelongsToMany
    {
        return $this->belongsToMany(RawMaterial::class, 'variation_option_ingredients')
                    ->withPivot('action')
                    ->withTimestamps();
    }
}
