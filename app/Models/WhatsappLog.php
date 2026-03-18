<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $table = 'r_whatsapp_logs';

    protected $fillable = [
        'report_id',
        'phone_number',
        'message_body',
        'status',
        'sent_at'
    ];

    public function dailyReport()
    {
        return $this->belongsTo(DailyReport::class, 'report_id');
    }
}
