<?php

namespace App\Filament\Merchant\Resources\ProductResource\Pages;

use App\Filament\Merchant\Resources\ProductResource;
use App\Models\StockLog;
use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected bool $stockWasAdded = false;

    protected function handleRecordCreation(array $data): Model
    {
        $product = $this->findExistingProduct($data);

        if ($product) {
            $increment = max(1, (int) ($data['stock_increment'] ?? 1));
            $stockBefore = (int) $product->stock;

            $product->increment('stock', $increment);
            $product->refresh();

            StockLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'quantity_change' => $increment,
                'stock_before' => $stockBefore,
                'stock_after' => (int) $product->stock,
                'type' => 'in',
                'note' => 'Tambah stok dari form produk',
            ]);

            $this->stockWasAdded = true;

            Notification::make()
                ->title('Stok berhasil ditambahkan')
                ->body("Stok {$product->name} sekarang {$product->stock}.")
                ->success()
                ->send();

            return $product;
        }

        $data['merchant_id'] = filament()->getTenant()?->id;
        unset($data['existing_product_id'], $data['stock_increment']);

        $product = static::getModel()::create($data);
        $initialStock = (int) ($product->stock ?? 0);

        if ($initialStock > 0) {
            StockLog::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'quantity_change' => $initialStock,
                'stock_before' => 0,
                'stock_after' => $initialStock,
                'type' => 'in',
                'note' => 'Stok awal produk',
            ]);
        }

        return $product;
    }

    private function findExistingProduct(array $data): ?Product
    {
        $query = Product::query()
            ->where('merchant_id', filament()->getTenant()?->id);

        if (!empty($data['existing_product_id'])) {
            return (clone $query)->find($data['existing_product_id']);
        }

        if (!empty($data['barcode'])) {
            return (clone $query)
                ->where('barcode', trim((string) $data['barcode']))
                ->first();
        }

        return null;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return $this->stockWasAdded
            ? null
            : 'Produk berhasil ditambahkan';
    }
}
