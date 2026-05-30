<?php

namespace App\Filament\Merchant\Resources\PurchaseResource\Pages;

use App\Filament\Merchant\Resources\PurchaseResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchase extends CreateRecord
{
    protected static string $resource = PurchaseResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return PurchaseResource::createPurchaseWithStock($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pembelian berhasil disimpan dan stok sudah diperbarui';
    }
}
