<?php

namespace App\Filament\Merchant\Resources\CashierResource\Pages;

use App\Filament\Merchant\Resources\CashierResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCashier extends EditRecord
{
    protected static string $resource = CashierResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Hapus Kasir'),
        ];
    }

    /**
     * Pastikan merchant_id tidak bisa diubah dari luar
     * dan role cashier tetap terjaga saat update.
     */
    protected function afterSave(): void
    {
        $merchant = filament()->getTenant();

        // Pastikan relasi merchant dan role tetap terikat
        $this->record->update([
            'merchant_id' => $merchant?->id,
            'role'        => 'cashier',
        ]);
    }
}
