<?php

namespace App\Filament\Merchant\Resources\DailyReportResource\Pages;

use App\Filament\Merchant\Resources\DailyReportResource;
use Filament\Resources\Pages\ListRecords;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
