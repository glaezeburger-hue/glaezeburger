<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'reward_type',
        'reward_value',
        'free_product_id',
        'min_purchase',
        'max_discount',
        'quota',
        'used_count',
        'valid_from',
        'valid_until',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function freeProduct()
    {
        return $this->belongsTo(Product::class, 'free_product_id');
    }
}
