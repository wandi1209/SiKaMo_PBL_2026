<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\TransactionItem;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopProductsChart extends ChartWidget
{
    protected static ?string $heading = 'Produk Terlaris';
    protected static ?int $sort = 3;
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 1;

    public static function canView(): bool
    {
        return auth()->user()?->role === 'owner';
    }

    protected function getData(): array
    {
        $merchantId = filament()->getTenant()?->id;

        $products = $merchantId
            ? TransactionItem::query()
                ->select('m_products.name', DB::raw('SUM(t_transaction_items.quantity) as total_quantity'))
                ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
                ->join('t_transactions', 't_transactions.id', '=', 't_transaction_items.transaction_id')
                ->where('m_products.merchant_id', $merchantId)
                ->where('t_transactions.status', 'completed')
                ->groupBy('m_products.id', 'm_products.name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get()
            : collect();

        return [
            'datasets' => [
                [
                    'label' => 'Terjual',
                    'data' => $products->pluck('total_quantity')->map(fn ($quantity) => (int) $quantity)->all(),
                    'backgroundColor' => '#378ADD',
                    'borderRadius' => 6,
                ],
            ],
            'labels' => $products->pluck('name')->all(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
