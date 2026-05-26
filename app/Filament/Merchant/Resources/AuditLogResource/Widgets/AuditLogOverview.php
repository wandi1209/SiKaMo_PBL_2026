<?php

namespace App\Filament\Merchant\Resources\AuditLogResource\Widgets;

use App\Filament\Merchant\Resources\AuditLogResource;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;

class AuditLogOverview extends Widget
{
    protected static string $view = 'filament.merchant.resources.audit-log-resource.widgets.audit-log-overview';

    protected int|string|array $columnSpan = 'full';

    public function getTotalToday(): int
    {
        return AuditLogResource::getEloquentQuery()
            ->whereDate('created_at', today())
            ->count();
    }

    public function getPendingFlags(): int
    {
        $query = AuditLogResource::flaggedQuery(AuditLogResource::getEloquentQuery());

        if (Schema::hasColumn('audit_logs', 'reviewed_at')) {
            $query->whereNull('reviewed_at');
        }

        return $query->count();
    }

    public function getActiveUsers(): int
    {
        return AuditLogResource::getEloquentQuery()
            ->whereDate('created_at', today())
            ->whereNotNull('user_name')
            ->distinct('user_name')
            ->count('user_name');
    }
}
