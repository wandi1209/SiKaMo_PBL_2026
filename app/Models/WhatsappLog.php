<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappLog extends Model
{
    protected $table = 'r_whatsapp_logs';

    protected $fillable = [
        'merchant_id',
        'report_id',
        'phone_number',
        'message_body',
        'status',
        'provider',
        'provider_request_id',
        'provider_message_ids',
        'response_body',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'provider_message_ids' => 'array',
        'response_body' => 'array',
        'sent_at' => 'datetime',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function dailyReport(): BelongsTo
    {
        return $this->belongsTo(DailyReport::class, 'report_id');
    }
}
