<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'address',
        'city',
        'phone',
        'receipt_header',
        'receipt_footer',
        'receipt_instagram',
        'receipt_tiktok',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ── Relationships ──────────────────────────────

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function cashRegisters()
    {
        return $this->hasMany(CashRegister::class);
    }

    public function rawMaterials()
    {
        return $this->hasMany(RawMaterial::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function wastages()
    {
        return $this->hasMany(Wastage::class);
    }

    // ── Scopes ──────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // ── Helpers ──────────────────────────────────────

    /**
     * Get the full location string for display.
     */
    public function getFullAddressAttribute(): string
    {
        return collect([$this->address, $this->city])
            ->filter()
            ->implode(', ');
    }
}
