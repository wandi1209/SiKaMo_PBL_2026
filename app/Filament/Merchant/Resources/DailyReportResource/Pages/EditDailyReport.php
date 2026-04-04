<?php

namespace App\Filament\Merchant\Resources\DailyReportResource\Pages;

use App\Filament\Merchant\Resources\DailyReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyReport extends EditRecord
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
