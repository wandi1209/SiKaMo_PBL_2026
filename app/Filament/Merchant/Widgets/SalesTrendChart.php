<?php

namespace App\Filament\Merchant\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;

class SalesTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Omzet 7 Hari Terakhir';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';
    protected static ?string $maxHeight = '320px';

    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $merchantId = filament()->getTenant()?->id;
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->translatedFormat('d M');

            $data[] = $merchantId
                ? (float) $this->completedTransactions($merchantId)
                    ->whereDate('created_at', $date)
                    ->sum('total_amount')
                : 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Omzet',
                    'data' => $data,
                    'borderColor' => '#1D9E75',
                    'backgroundColor' => 'rgba(29, 158, 117, 0.16)',
                    'fill' => true,
                    'tension' => 0.35,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    private function completedTransactions(int $merchantId): Builder
    {
        return Transaction::query()
            ->where('status', 'completed')
            ->whereHas('items.product', fn (Builder $query) => $query->where('merchant_id', $merchantId));
    }
}
