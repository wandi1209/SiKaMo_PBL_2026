<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'role',
        'merchant_id', // untuk kasir: relasi ke merchant tempat bertugas
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // ─── Relationships ────────────────────────────────────────────

    /** Merchant yang dimiliki user ini (jika role = owner) */
    public function merchant(): HasOne
    {
        return $this->hasOne(Merchant::class, 'owner_id');
    }

    /** Merchant tempat user ini bertugas (jika role = cashier) */
    public function employedAt(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }
    // ─── Filament Panel Access ────────────────────────────────────

    public function canAccessPanel(Panel $panel): bool
    {
        $isActive = $this->is_active ?? true;

        if ($panel->getId() === 'auth') {
            return true;
        }

        if ($panel->getId() === 'admin') {
            return $this->role === 'admin' && $isActive;
        }

        if ($panel->getId() === 'merchant') {
            return in_array($this->role, ['owner', 'cashier']) && $isActive;
        }

        return false;
    }

    // ─── Filament HasTenants Interface ────────────────────────────

    public function getTenants(Panel $panel): array|Collection
    {
        if ($this->role === 'owner' && $this->merchant) {
            return collect([$this->merchant]);
        }

        if ($this->role === 'cashier' && $this->employedAt) {
            return collect([$this->employedAt]);
        }

        return collect();
    }

    public function canAccessTenant(Model $tenant): bool
    {
        if ($this->role === 'owner') {
            return $this->merchant?->id === $tenant->id;
        }

        if ($this->role === 'cashier') {
            return $this->merchant_id === $tenant->id;
        }

        return false;
    }

    // ─── Role Helpers ────────────────────────────────────────────
    
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }
}


