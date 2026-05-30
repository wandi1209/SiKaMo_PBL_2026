<?php

namespace App\Filament\Merchant\Resources\MemberResource\Pages;

use App\Filament\Merchant\Resources\MemberResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateMember extends CreateRecord
{
    protected static string $resource = MemberResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['merchant_id'] = filament()->getTenant()?->id;

        return static::getModel()::create($data);
    }
}
