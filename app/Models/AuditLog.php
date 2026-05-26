<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'merchant_id',
        'user_name',
        'user_email',
        'user_role',
        'event',
        'auditable_type',
        'auditable_table',
        'auditable_id',
        'auditable_label',
        'old_values',
        'new_values',
        'changed_fields',
        'summary',
        'request_method',
        'request_url',
        'route_name',
        'panel',
        'request_id',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'changed_fields' => 'array',
        'metadata' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
