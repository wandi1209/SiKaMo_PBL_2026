<?php

namespace App\Filament\Merchant\Resources\AuditLogResource\Pages;

use App\Filament\Merchant\Resources\AuditLogResource;
use App\Filament\Merchant\Resources\AuditLogResource\Widgets\AuditLogOverview;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListAuditLogs extends ListRecords
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            AuditLogOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua')
                ->icon('heroicon-m-users')
                ->badge(fn (): int => AuditLogResource::getEloquentQuery()->count()),

            'kasir' => Tab::make('Kasir')
                ->icon('heroicon-m-user')
                ->badge(fn (): int => AuditLogResource::getEloquentQuery()->where('user_role', 'cashier')->count())
                ->badgeColor('info')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('user_role', 'cashier')),

            'owner' => Tab::make('Owner')
                ->icon('heroicon-m-shield-check')
                ->badge(fn (): int => AuditLogResource::getEloquentQuery()->where('user_role', 'owner')->count())
                ->badgeColor('success')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where('user_role', 'owner')),

            'system' => Tab::make('System')
                ->icon('heroicon-m-cog-6-tooth')
                ->badge(fn (): int => AuditLogResource::getEloquentQuery()
                    ->where(fn (Builder $query): Builder => $query->whereNull('user_role')->orWhere('user_role', 'system'))
                    ->count())
                ->badgeColor('gray')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                    $query->whereNull('user_role')->orWhere('user_role', 'system');
                })),
        ];
    }
}
