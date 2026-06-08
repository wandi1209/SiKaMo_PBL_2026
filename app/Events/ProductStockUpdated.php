<?php

namespace App\Events;

use App\Models\Product;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Throwable;

class ProductStockUpdated implements ShouldBroadcastNow
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $merchantId,
        public int $productId,
        public string $productName,
        public int $stock,
        public int $lowStockThreshold,
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        return new self(
            merchantId: (int) $product->merchant_id,
            productId: (int) $product->id,
            productName: (string) $product->name,
            stock: (int) $product->stock,
            lowStockThreshold: (int) $product->low_stock_threshold,
        );
    }

    public static function dispatchSafely(Product $product): void
    {
        try {
            event(self::fromProduct($product));
        } catch (Throwable $exception) {
            Log::warning('Realtime stok produk gagal dikirim.', [
                'product_id' => $product->id,
                'merchant_id' => $product->merchant_id,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    public function broadcastOn(): PrivateChannel
    {
        return new PrivateChannel('merchant.' . $this->merchantId);
    }

    public function broadcastAs(): string
    {
        return 'ProductStockUpdated';
    }

    public function broadcastWith(): array
    {
        return [
            'productId' => $this->productId,
            'productName' => $this->productName,
            'stock' => $this->stock,
            'lowStockThreshold' => $this->lowStockThreshold,
            'isLowStock' => $this->stock <= $this->lowStockThreshold,
        ];
    }
}
