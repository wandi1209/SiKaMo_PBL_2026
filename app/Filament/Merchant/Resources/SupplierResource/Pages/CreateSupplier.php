<?php

namespace App\Filament\Merchant\Resources\SupplierResource\Pages;

use App\Filament\Merchant\Resources\SupplierResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['merchant_id'] = filament()->getTenant()?->id;

        return static::getModel()::create($data);
    }
}
