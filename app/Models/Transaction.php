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
        'invoice_number',
        'total_amount',
        'amount_paid',
        'change_amount',
        'payment_method',
        'status',
    ];

    // Relasi ke user
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
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
