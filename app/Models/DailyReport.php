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
        'pdf_path',
        'pdf_generated_at',
        'sent_to_whatsapp',
        'sent_at',
    ];

    protected $casts = [
        'report_date' => 'date',
        'sent_to_whatsapp' => 'boolean',
        'pdf_generated_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
