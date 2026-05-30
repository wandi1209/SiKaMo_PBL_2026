<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Merchant extends Model
{
    protected $table = 'm_merchants';

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'phone',
        'address',
        'logo',
        'operational_start_time',
        'operational_end_time',
        'qr_code_image',
        'qris_payload',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Auto-generate slug dari name ────────────────────────────
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $merchant) {
            if (empty($merchant->slug)) {
                $merchant->slug = Str::slug($merchant->name);
            }
        });
    }

    // ─── Relationships ────────────────────────────────────────────

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function purchases(): HasMany
    {
        return $this->hasMany(Purchase::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(WhatsappLog::class);
    }

    /** Semua kasir yang bertugas di merchant ini */
    public function cashiers(): HasMany
    {
        return $this->hasMany(User::class, 'merchant_id');
    }

}
