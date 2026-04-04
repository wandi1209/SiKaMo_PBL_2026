<?php

namespace App\Filament\Merchant\Resources\CashierResource\Pages;

use App\Filament\Merchant\Resources\CashierResource;
use Filament\Resources\Pages\ListRecords;

class ListCashiers extends ListRecords
{
    protected static string $resource = CashierResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Tambah Kasir'),
        ];
    }
}
