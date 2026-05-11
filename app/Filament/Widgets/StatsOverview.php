<?php

namespace App\Filament\Widgets;

use App\Models\Merchant;
use App\Models\Transaction;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->format('d M Y');
        $transactionsToday = Transaction::whereDate('created_at', today())->where('status', 'completed')->count();
        $revenueToday = Transaction::whereDate('created_at', today())->where('status', 'completed')->sum('total_amount');
        $revenueMonth = Transaction::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])->where('status', 'completed')->sum('total_amount');

        return [
            Stat::make('Merchant Aktif', Merchant::where('is_active', true)->count() . ' / ' . Merchant::count())
                ->description('Aktif dari total merchant')
                ->descriptionIcon('heroicon-m-building-storefront')
                ->color('primary'),

            Stat::make('Transaksi Hari Ini', number_format($transactionsToday, 0, ',', '.'))
                ->description("Per $today")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Stat::make('Omzet Hari Ini', 'Rp ' . number_format($revenueToday, 0, ',', '.'))
                ->description("Per $today")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Omzet Bulan Ini', 'Rp ' . number_format($revenueMonth, 0, ',', '.'))
                ->description('Semua merchant')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('success'),

            Stat::make('Total User', number_format(User::count(), 0, ',', '.'))
                ->description('Admin, owner, dan kasir')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
        ];
    }
}
