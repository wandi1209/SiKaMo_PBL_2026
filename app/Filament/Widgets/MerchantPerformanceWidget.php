<?php

namespace App\Filament\Widgets;

use App\Models\Merchant;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class MerchantPerformanceWidget extends BaseWidget
{
    protected static ?string $heading = 'Monitoring Operasional Merchant';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        // Tenant isolation (UU PDP): the platform admin must NOT see per-tenant
        // revenue / sales figures. Only operational health (status, stock) is
        // exposed here so support can help without accessing tenant omzet.
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

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),

                Tables\Columns\TextColumn::make('low_stock')
                    ->label('Stok Menipis')
                    ->state(fn (Merchant $record): int => $record->products()->whereColumn('stock', '<=', 'low_stock_threshold')->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),
            ])
            ->paginated(false);
    }
}
