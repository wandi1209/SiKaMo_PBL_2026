<?php

namespace App\Filament\Resources\AdminResource\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class TransactionChartWidget extends ChartWidget
{
    protected static ?string $heading = 'Grafik Penjualan 7 Hari Terakhir';
    protected static ?string $pollingInterval = '10s';
    protected int | string | array $columnSpan = 'full';
    protected static ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $labels = [];
        $data = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $labels[] = $date->format('d M');

            $data[] = (float) Transaction::query()
                ->where('status', 'completed')
                ->whereDate('created_at', $date)
                ->sum('total_amount');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Penjualan (Rp)',
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
}
