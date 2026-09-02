<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'wa_account_id',
        'session_id',
        'wa_status',
        'wa_phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function waAccount()
    {
        return $this->belongsTo(WaAccount::class);
    }

    public function supervisedBrands()
    {
        return $this->belongsToMany(WaAccount::class, 'brand_supervisors', 'user_id', 'wa_account_id')->withTimestamps();
    }

    public function isGodAdmin(): bool
    {
        return strtoupper($this->role) === 'GOD_ADMIN' || $this->email === 'ashabil@difitech.co.id';
    }

    public function isCeo(): bool
    {
        return strtoupper($this->role) === 'CEO' || $this->isGodAdmin();
    }

    public function isSupervisor(): bool
    {
        return strtoupper($this->role) === 'SUPERVISOR';
    }

    public function isSalesAdmin(): bool
    {
        return strtoupper($this->role) === 'SALES_ADMIN';
    }

    public function isApproved(): bool
    {
        return $this->isGodAdmin() || $this->status === 'APPROVED';
    }

    public function getAccessibleBrands()
    {
        if ($this->isCeo() || $this->isGodAdmin()) {
            return WaAccount::where('approval_status', 'APPROVED')->get();
        }

        if ($this->isSupervisor()) {
            $brands = $this->supervisedBrands()->where('approval_status', 'APPROVED')->get();
            if ($brands->isEmpty() && $this->wa_account_id) {
                $fallback = WaAccount::where('id', $this->wa_account_id)->where('approval_status', 'APPROVED')->get();
                return $fallback;
            }
            return $brands;
        }

        return $this->wa_account_id ? WaAccount::where('id', $this->wa_account_id)->where('approval_status', 'APPROVED')->get() : collect();
    }

    public function canAccessBrand($accountId): bool
    {
        if ($this->isCeo() || $this->isGodAdmin()) {
            return true;
        }

        if ($accountId === 'all') {
            return $this->isCeo() || ($this->isSupervisor() && $this->getAccessibleBrands()->count() > 1);
        }

        return $this->getAccessibleBrands()->contains('id', (int)$accountId);
    }
}
