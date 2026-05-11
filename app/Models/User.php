<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
        'branch_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'status' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    // ── Relationships ──────────────────────────────

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // ── Role Checks ────────────────────────────────
    // Role Hierarchy: super_owner -> owner -> cashier
    // cashier now performs both front-of-house (POS) and back-of-house (KDS) duties.

    public function isSuperOwner(): bool
    {
        return $this->role === 'super_owner';
    }

    public function isOwner(): bool
    {
        return in_array($this->role, ['owner', 'super_owner']);
    }

    public function isCashier(): bool
    {
        // cashier is the unified role for both POS and KDS operations
        // including 'kitchen' here to ensure existing kitchen staff get unified access
        return in_array($this->role, ['cashier', 'kitchen']);
    }

    public function isKitchen(): bool
    {
        // Legacy support: kitchen role is being consolidated into cashier
        return in_array($this->role, ['kitchen', 'cashier']);
    }

    /**
     * Check if user has any of the given roles.
     */
    public function hasRole(string ...$roles): bool
    {
        return in_array($this->role, $roles);
    }
}
