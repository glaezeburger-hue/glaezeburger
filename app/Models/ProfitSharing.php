<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfitSharing extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'gross_revenue',
        'investor_share_amount'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
