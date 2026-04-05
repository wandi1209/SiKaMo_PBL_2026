<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relasi ke item transaksi
    public function items()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id');
    }
}