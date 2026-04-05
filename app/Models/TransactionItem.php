<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionItem extends Model
{
    protected $table = 't_transaction_items';

    protected $fillable = [
        'transaction_id',
        'product_id',
        'quantity',
        'unit_price',
        'subtotal',
    ];

    // Relasi ke transaksi
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}