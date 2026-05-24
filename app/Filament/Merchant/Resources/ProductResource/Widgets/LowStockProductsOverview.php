<?php

namespace App\Filament\Merchant\Resources\ProductResource\Widgets;

use App\Models\Product;
use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LowStockProductsOverview extends Widget
{
     protected static string $view = 'filament.merchant.resources.product-resource.widgets.low-stock-products-overview';

     protected int|string|array $columnSpan = 'full';

     public function getLowStockProducts(): Collection
     {
          $merchantId = filament()->getTenant()?->id;

          if (!$merchantId) {
               return collect();
          }

          return Product::query()
               ->with('category')
               ->where('merchant_id', $merchantId)
               ->where(fn(Builder $query) => $this->scopeLowStockQuery($query))
               ->orderBy('stock')
               ->orderBy('name')
               ->limit(8)
               ->get();
     }

     public function getLowStockProductsCount(): int
     {
          $merchantId = filament()->getTenant()?->id;

          if (!$merchantId) {
               return 0;
          }

          return Product::query()
               ->where('merchant_id', $merchantId)
               ->where(fn(Builder $query) => $this->scopeLowStockQuery($query))
               ->count();
     }

     public function getEffectiveThreshold(Product $product): int
     {
          return (int) $product->low_stock_threshold;
     }

     private function scopeLowStockQuery(Builder $query): Builder
     {
          return $query->whereColumn('stock', '<=', 'low_stock_threshold');
     }
}
