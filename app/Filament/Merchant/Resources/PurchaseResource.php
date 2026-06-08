<?php

namespace App\Filament\Merchant\Resources;

use App\Events\ProductStockUpdated;
use App\Filament\Merchant\Resources\PurchaseResource\Pages;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class PurchaseResource extends Resource
{
    protected static ?string $model = Purchase::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel = 'Pembelian';
    protected static ?string $modelLabel = 'Pembelian';
    protected static ?string $pluralModelLabel = 'Pembelian';
    protected static ?string $navigationGroup = 'Inventori';
    protected static ?int $navigationSort = 4;
    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'owner';
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['supplier', 'user', 'items.product'])
            ->where('merchant_id', filament()->getTenant()?->id);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Pembelian')
                ->schema([
                    Forms\Components\Select::make('supplier_id')
                        ->label('Supplier')
                        ->options(fn(): array => Supplier::query()
                            ->where('merchant_id', filament()->getTenant()?->id)
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->required()
                        ->searchable()
                        ->preload(),

                    Forms\Components\DatePicker::make('purchase_date')
                        ->label('Tanggal Pembelian')
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('invoice_number')
                        ->label('No. Faktur')
                        ->maxLength(255),

                    Forms\Components\Textarea::make('notes')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Item Pembelian')
                ->schema([
                    Forms\Components\Placeholder::make('items_summary')
                        ->label('Produk')
                        ->content(fn(?Purchase $record): string => $record?->items
                            ->map(fn(PurchaseItem $item): string => sprintf(
                                '%s x%s @ Rp %s = Rp %s',
                                $item->product?->name ?? 'Produk Terhapus',
                                number_format($item->quantity, 0, ',', '.'),
                                number_format((float) $item->unit_cost, 0, ',', '.'),
                                number_format((float) $item->subtotal, 0, ',', '.'),
                            ))
                            ->implode("\n") ?: '-')
                        ->visible(fn(string $operation): bool => $operation === 'view')
                        ->columnSpanFull(),

                    Forms\Components\Repeater::make('items')
                        ->label('Produk')
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->label('Produk')
                                ->options(fn(): array => Product::query()
                                    ->where('merchant_id', filament()->getTenant()?->id)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->all())
                                ->required()
                                ->searchable()
                                ->preload(),

                            Forms\Components\TextInput::make('quantity')
                                ->label('Qty')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required(),

                            Forms\Components\TextInput::make('unit_cost')
                                ->label('Harga Beli')
                                ->numeric()
                                ->prefix('Rp')
                                ->minValue(0)
                                ->required(),
                        ])
                        ->columns(3)
                        ->minItems(1)
                        ->required()
                        ->visible(fn(string $operation): bool => $operation !== 'view')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('purchase_date')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Faktur')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Pembelian')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Dicatat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(fn(): array => Supplier::query()
                        ->where('merchant_id', filament()->getTenant()?->id)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),

                Tables\Filters\Filter::make('purchase_date')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('end_date')->label('Sampai Tanggal'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when($data['start_date'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '>=', $date))
                        ->when($data['end_date'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('purchase_date', '<=', $date))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('purchase_date', 'desc');
    }

    public static function createPurchaseWithStock(array $data): Purchase
    {
        $merchantId = filament()->getTenant()?->id;
        $userId = auth()->id();
        $items = $data['items'] ?? [];

        return DB::transaction(function () use ($data, $items, $merchantId, $userId): Purchase {
            $totalAmount = collect($items)->sum(fn(array $item): float => ((float) $item['unit_cost']) * ((int) $item['quantity']));

            $purchase = Purchase::create([
                'merchant_id' => $merchantId,
                'supplier_id' => $data['supplier_id'],
                'user_id' => $userId,
                'invoice_number' => $data['invoice_number'] ?? null,
                'purchase_date' => $data['purchase_date'],
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $product = Product::query()
                    ->where('merchant_id', $merchantId)
                    ->lockForUpdate()
                    ->findOrFail($item['product_id']);

                $quantity = (int) $item['quantity'];
                $unitCost = (float) $item['unit_cost'];
                $subtotal = $unitCost * $quantity;
                $stockBefore = (int) $product->stock;
                $stockAfter = $stockBefore + $quantity;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $unitCost,
                    'subtotal' => $subtotal,
                ]);

                $product->forceFill([
                    'supplier_id' => $data['supplier_id'],
                    'cost_price' => $unitCost,
                    'stock' => $stockAfter,
                ])->save();

                $product->refresh();
                DB::afterCommit(fn() => ProductStockUpdated::dispatchSafely($product));

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id' => $userId,
                    'quantity_change' => $quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'type' => 'in',
                    'note' => 'Pembelian ' . ($purchase->invoice_number ?: '#' . $purchase->id),
                ]);
            }

            return $purchase->refresh();
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchases::route('/'),
            'create' => Pages\CreatePurchase::route('/create'),
            'view' => Pages\ViewPurchase::route('/{record}'),
        ];
    }
}
