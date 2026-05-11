<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BranchScope;

class Transaction extends Model
{
    use HasFactory, BranchScope;

    protected $fillable = [
        'invoice_number',
        'customer_name',
        'user_id',
        'branch_id',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'net_sales',
        'tax_amount',
        'total_amount',
        'voucher_id',
        'voucher_discount_amount',
        'payment_method',
        'payment_status',
        'order_status',
        'is_imported',
        'payment_reference',
        'cash_register_id'
    ];

    protected $casts = [
        'is_imported' => 'boolean'
    ];

    public function items()
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function transactionItems()
    {
        return $this->items();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    public function cashRegister()
    {
        return $this->belongsTo(CashRegister::class);
    }

    /**
     * Generate a unique invoice number with branch code prefix.
     */
    public static function generateInvoiceNumber(?string $branchCode = null)
    {
        $code = $branchCode ?: (session('current_branch_code') ?? 'GEN');
        $date = now()->format('Ymd');
        
        $lastTransaction = self::withoutGlobalScopes()
            ->where('invoice_number', 'like', $code . '-' . $date . '-%')
            ->latest()
            ->first();

        $sequence = $lastTransaction 
            ? (int) substr($lastTransaction->invoice_number, -4) + 1 
            : 1;
        
        return $code . '-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
