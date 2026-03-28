<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariationOption extends Model
{
    protected $fillable = [
        'variation_group_id',
        'name',
        'short_name',
        'price_modifier',
        'is_default',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'price_modifier' => 'decimal:2',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    public function group(): BelongsTo
    {
        return $this->belongsTo(VariationGroup::class, 'variation_group_id');
    }
}
