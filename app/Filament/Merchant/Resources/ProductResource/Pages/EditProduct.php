<?php

namespace App\Filament\Merchant\Resources\ProductResource\Pages;

use App\Filament\Merchant\Resources\ProductResource;
use App\Models\StockLog;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected ?int $stockBeforeSave = null;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        $this->stockBeforeSave = (int) $this->record->getOriginal('stock');
    }

    protected function afterSave(): void
    {
        $stockAfter = (int) $this->record->stock;
        $stockBefore = $this->stockBeforeSave;

        if ($stockBefore === null || $stockBefore === $stockAfter) {
            return;
        }

        StockLog::create([
            'product_id' => $this->record->id,
            'user_id' => auth()->id(),
            'quantity_change' => $stockAfter - $stockBefore,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'type' => 'adjustment',
            'note' => 'Koreksi stok manual dari edit produk',
        ]);
    }
}
