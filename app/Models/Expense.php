<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = [
        'expense_category_id', 'user_id', 'description', 
        'amount', 'expense_date', 'payment_method', 
        'receipt_image', 'notes'
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category()
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function restockItems()
    {
        return $this->hasMany(ExpenseRestockItem::class);
    }

    public function isRestock()
    {
        return $this->category && $this->category->is_restock;
    }
}
