<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    protected $table = 'm_products';

    protected $fillable = [
        'category_id',
        'name',
        'barcode',
        'price',
        'stock',
        'low_stock_threshold',
        'image'
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
