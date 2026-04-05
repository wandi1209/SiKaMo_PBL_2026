<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    protected $table = 't_stock_logs';

    protected $fillable = [
        'product_id',
        'user_id',
        'quantity_change',
        'stock_before',
        'stock_after',
        'type',
        'note',
    ];

    // Relasi ke produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}