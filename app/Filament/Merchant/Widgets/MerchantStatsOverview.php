<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\Product;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;

class MerchantStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $merchantId = filament()->getTenant()?->id;
        $today = now();

        if (!$merchantId) {
            return [];
        }

        $transactionsToday = $this->completedTransactions($merchantId)
            ->whereDate('created_at', $today)
            ->count();

        $revenueToday = $this->completedTransactions($merchantId)
            ->whereDate('created_at', $today)
            ->sum('total_amount');

        $lowStockProducts = Product::query()
            ->where('merchant_id', $merchantId)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();

        return [
            Stat::make('Transaksi Hari Ini', number_format($transactionsToday, 0, ',', '.'))
                ->description($today->translatedFormat('d M Y'))
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('primary'),

            Stat::make('Omzet Hari Ini', 'Rp ' . number_format($revenueToday, 0, ',', '.'))
                ->description('Transaksi selesai')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Produk Stok Menipis', number_format($lowStockProducts, 0, ',', '.'))
                ->description('Stok <= batas minimum')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'danger' : 'success'),
        ];
    }

    private function completedTransactions(int $merchantId): Builder
    {
        return Transaction::query()
            ->where('status', 'completed')
            ->whereHas('items.product', fn (Builder $query) => $query->where('merchant_id', $merchantId));
    }
}
