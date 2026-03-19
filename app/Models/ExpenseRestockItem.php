<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseRestockItem extends Model
{
    protected $fillable = [
        'expense_id', 'raw_material_id', 'quantity', 'unit_cost'
    ];

    public function expense()
    {
        return $this->belongsTo(Expense::class);
    }

    public function rawMaterial()
    {
        return $this->belongsTo(RawMaterial::class);
    }
}
