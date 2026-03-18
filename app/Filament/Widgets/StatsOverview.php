<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $today = now()->format('d M Y');

        return [

            Card::make('Total Transaksi Hari Ini', 32)
                ->description("Per $today")
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),

            Card::make('Omzet Hari Ini', 'Rp 2.450.000')
                ->description("Per $today")
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Card::make('Produk Stok Menipis', 5)
                ->description('Stok < 5')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
