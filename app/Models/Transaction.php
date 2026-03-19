<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'user_id',
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
     * Generate a unique invoice number.
     */
    public static function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $lastTransaction = self::whereDate('created_at', now()->toDateString())->latest()->first();
        $sequence = $lastTransaction ? (int) substr($lastTransaction->invoice_number, -4) + 1 : 1;
        
        return 'INV-' . $date . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
