<?php

namespace App\Filament\Merchant\Pages;

use Filament\Pages\Page;

class Sales extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationLabel = 'Penjualan';

    protected static ?string $title = 'Transaksi Penjualan';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?int $navigationSort = 1;

    protected static string $view = 'filament.merchant.pages.sales';
}
