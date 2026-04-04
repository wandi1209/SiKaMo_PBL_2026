<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyReport extends Model
{
    protected $table = 'r_daily_reports';

    protected $fillable = [
        'merchant_id',
        'report_date',
        'total_transactions',
        'total_revenue',
        'total_items_sold',
        'sent_to_whatsapp',
        'sent_at',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function whatsappLogs(): HasMany
    {
        return $this->hasMany(WhatsappLog::class, 'report_id');
    }
}

