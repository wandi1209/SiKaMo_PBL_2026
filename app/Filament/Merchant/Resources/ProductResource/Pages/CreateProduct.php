<?php

namespace App\Filament\Merchant\Resources\ProductResource\Pages;

use App\Filament\Merchant\Resources\ProductResource;
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

            $product->increment('stock', $increment);
            $product->refresh();

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

        return static::getModel()::create($data);
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
