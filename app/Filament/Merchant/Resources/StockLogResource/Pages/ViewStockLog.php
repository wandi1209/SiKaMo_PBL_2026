<?php

namespace App\Filament\Merchant\Resources\StockLogResource\Pages;

use App\Filament\Merchant\Resources\StockLogResource;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\ViewRecord;

class ViewStockLog extends ViewRecord
{
    protected static string $resource = StockLogResource::class;

    public function getTitle(): string
    {
        return 'Detail Riwayat Stok';
    }

    protected function resolveRecord(int | string $key): Model
    {
        return parent::resolveRecord($key)->loadMissing(['product', 'user']);
    }
}
