<?php

namespace App\Filament\Widgets;

use App\Models\Merchant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\DB;

class MerchantPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Performa Merchant Bulan Ini';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(Merchant::query()->with('owner'))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Merchant')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('revenue')
                    ->label('Omzet')
                    ->state(fn (Merchant $record): float => $this->monthlyRevenue($record))
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('transactions')
                    ->label('Transaksi')
                    ->state(fn (Merchant $record): int => $this->monthlyTransactions($record))
                    ->badge(),

                Tables\Columns\TextColumn::make('low_stock')
                    ->label('Stok Menipis')
                    ->state(fn (Merchant $record): int => $record->products()->whereColumn('stock', '<=', 'low_stock_threshold')->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),
            ])
            ->paginated(false);
    }

    private function monthlyRevenue(Merchant $merchant): float
    {
        return (float) DB::query()
            ->fromSub($this->merchantTransactionsQuery($merchant), 'merchant_transactions')
            ->sum('total_amount');
    }

    private function monthlyTransactions(Merchant $merchant): int
    {
        return DB::query()
            ->fromSub($this->merchantTransactionsQuery($merchant), 'merchant_transactions')
            ->count();
    }

    private function merchantTransactionsQuery(Merchant $merchant)
    {
        return DB::table('t_transactions')
            ->select('t_transactions.id', 't_transactions.total_amount')
            ->join('t_transaction_items', 't_transaction_items.transaction_id', '=', 't_transactions.id')
            ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
            ->where('m_products.merchant_id', $merchant->id)
            ->where('t_transactions.status', 'completed')
            ->whereBetween('t_transactions.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('t_transactions.id', 't_transactions.total_amount');
    }
}
