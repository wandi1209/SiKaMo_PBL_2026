<?php

namespace App\Filament\Merchant\Resources\CashierResource\Pages;

use App\Filament\Merchant\Resources\CashierResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCashier extends CreateRecord
{
    protected static string $resource = CashierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Setelah kasir dibuat, otomatis:
     * 1. Assign role 'cashier'
     * 2. Pastikan merchant_id terisi dari tenant aktif
     */
    protected function afterCreate(): void
    {
        $merchant = filament()->getTenant();

        $this->record->update([
            'merchant_id' => $merchant?->id,
            'role'        => 'cashier',
        ]);
    }
}
