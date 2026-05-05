<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class PaymentMethodChart extends ChartWidget
{
    protected static ?string $heading = 'Metode Pembayaran';
    protected static ?int $sort = 4;
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '300px';

    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $merchantId = filament()->getTenant()?->id;

        $cashTotal = $merchantId ? $this->completedTransactions($merchantId)->where('payment_method', 'cash')->count() : 0;
        $qrisTotal = $merchantId ? $this->completedTransactions($merchantId)->where('payment_method', 'qris')->count() : 0;

        return [
            'datasets' => [
                [
                    'label' => 'Transaksi',
                    'data' => [$cashTotal, $qrisTotal],
                    'backgroundColor' => ['#1D9E75', '#BA7517'],
                    'borderWidth' => 0,
                ],
            ],
            'labels' => ['Tunai', 'QRIS'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    private function completedTransactions(int $merchantId): Builder
    {
        return Transaction::query()
            ->where('status', 'completed')
            ->whereHas('items.product', fn (Builder $query) => $query->where('merchant_id', $merchantId));
    }
}
