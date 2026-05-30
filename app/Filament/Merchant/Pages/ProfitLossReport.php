<?php

namespace App\Filament\Merchant\Pages;

use App\Models\TransactionItem;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder;

class ProfitLossReport extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Laba/Rugi';

    protected static ?string $title = 'Laporan Laba/Rugi';

    protected static ?string $navigationGroup = 'Monitoring';

    protected static ?int $navigationSort = 2;

    protected static string $view = 'filament.merchant.pages.profit-loss-report';

    public ?string $startDate = null;

    public ?string $endDate = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->role === 'owner';
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getSummaryProperty(): array
    {
        $query = $this->baseQuery();

        $revenue = (float) (clone $query)->sum('subtotal');
        $cost = (float) (clone $query)->sum('total_cost');
        $profit = (float) (clone $query)->sum('gross_profit');
        $transactionCount = (clone $query)->distinct('transaction_id')->count('transaction_id');

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'margin' => $revenue > 0 ? ($profit / $revenue) * 100 : 0,
            'transaction_count' => $transactionCount,
            'items_sold' => (int) (clone $query)->sum('quantity'),
        ];
    }

    public function getSupplierRowsProperty(): array
    {
        return $this->baseQuery()
            ->with(['product.supplier'])
            ->get()
            ->groupBy(fn(TransactionItem $item): string => $item->product?->supplier?->name ?? 'Tanpa Supplier')
            ->map(fn($items, string $supplier): array => [
                'supplier' => $supplier,
                'revenue' => (float) $items->sum('subtotal'),
                'cost' => (float) $items->sum('total_cost'),
                'profit' => (float) $items->sum('gross_profit'),
                'quantity' => (int) $items->sum('quantity'),
            ])
            ->sortByDesc('profit')
            ->values()
            ->all();
    }

    public function getProductRowsProperty(): array
    {
        return $this->baseQuery()
            ->with(['product'])
            ->get()
            ->groupBy(fn(TransactionItem $item): string => $item->product?->name ?? 'Produk Terhapus')
            ->map(fn($items, string $product): array => [
                'product' => $product,
                'revenue' => (float) $items->sum('subtotal'),
                'cost' => (float) $items->sum('total_cost'),
                'profit' => (float) $items->sum('gross_profit'),
                'quantity' => (int) $items->sum('quantity'),
            ])
            ->sortByDesc('profit')
            ->take(10)
            ->values()
            ->all();
    }

    private function baseQuery(): Builder
    {
        $merchantId = filament()->getTenant()?->id;

        return TransactionItem::query()
            ->whereHas('product', fn(Builder $query): Builder => $query->where('merchant_id', $merchantId))
            ->whereHas('transaction', function (Builder $query): Builder {
                return $query
                    ->where('status', 'completed')
                    ->when($this->startDate, fn(Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                    ->when($this->endDate, fn(Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date));
            });
    }
}
