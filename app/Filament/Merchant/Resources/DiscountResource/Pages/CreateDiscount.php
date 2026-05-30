<?php

namespace App\Filament\Merchant\Resources\DiscountResource\Pages;

use App\Filament\Merchant\Resources\DiscountResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateDiscount extends CreateRecord
{
    protected static string $resource = DiscountResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['merchant_id'] = filament()->getTenant()?->id;

        return static::getModel()::create($data);
    }
}
