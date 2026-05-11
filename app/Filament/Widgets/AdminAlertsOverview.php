<?php

namespace App\Filament\Widgets;

use App\Models\Merchant;
use App\Models\Product;
use App\Models\WhatsappLog;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminAlertsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $failedWhatsapp = WhatsappLog::where('status', 'failed')->count();
        $inactiveMerchants = Merchant::where('is_active', false)->count();
        $lowStockProducts = Product::whereColumn('stock', '<=', 'low_stock_threshold')->count();

        return [
            Stat::make('WhatsApp Gagal', number_format($failedWhatsapp, 0, ',', '.'))
                ->description('Perlu dicek')
                ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                ->color($failedWhatsapp > 0 ? 'danger' : 'success'),

            Stat::make('Merchant Nonaktif', number_format($inactiveMerchants, 0, ',', '.'))
                ->description('Tidak bisa digunakan')
                ->descriptionIcon('heroicon-m-no-symbol')
                ->color($inactiveMerchants > 0 ? 'warning' : 'success'),

            Stat::make('Produk Stok Menipis', number_format($lowStockProducts, 0, ',', '.'))
                ->description('Semua merchant')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockProducts > 0 ? 'danger' : 'success'),
        ];
    }
}
