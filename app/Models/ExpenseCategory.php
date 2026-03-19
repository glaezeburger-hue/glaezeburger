<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExpenseCategory extends Model
{
    protected $fillable = ['name', 'icon', 'is_restock'];

    protected $casts = [
        'is_restock' => 'boolean'
    ];

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function scopeRestock($query)
    {
        return $query->where('is_restock', true);
    }
}
