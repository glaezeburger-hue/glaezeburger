<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BranchScope;

class Wastage extends Model
{
    use BranchScope;

    protected $fillable = [
        'raw_material_id', 'user_id', 'branch_id', 'quantity', 
        'reason', 'description', 'wastage_date'
    ];

    protected $casts = [
        'wastage_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted()
    {
        static::created(function ($wastage) {
            $wastage->rawMaterial->decrement('stock', $wastage->quantity);
        });

        static::deleted(function ($wastage) {
            $wastage->rawMaterial->increment('stock', $wastage->quantity);
        });
    }
}
