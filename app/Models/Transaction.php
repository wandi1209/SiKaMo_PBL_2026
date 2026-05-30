<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $table = 't_transactions';

    protected $fillable = [
        'user_id',
        'discount_id',
        'discount_code',
        'member_id',
        'member_name',
        'member_phone',
        'invoice_number',
        'subtotal_amount',
        'discount_amount',
        'member_discount_amount',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'status',
        'refunded_by',
        'refunded_at',
        'refund_reason',
    ];

    protected $casts = [
        'refunded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi ke user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function refundedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'refunded_by');
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    // Relasi ke item transaksi
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }

    public function getMerchantAttribute(): ?Merchant
    {
        if ($this->relationLoaded('items')) {
            return $this->items
                ->pluck('product.merchant')
                ->filter()
                ->first();
        }

        return $this->items()
            ->with('product.merchant')
            ->get()
            ->pluck('product.merchant')
            ->filter()
            ->first();
    }
}
