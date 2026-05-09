<?php

namespace App\Filament\Merchant\Resources\DailyReportResource\Pages;

use App\Filament\Merchant\Resources\DailyReportResource;
use App\Exports\DailyReportExport;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Tables\Contracts\HasTable;
use Maatwebsite\Excel\Facades\Excel;

class ListDailyReports extends ListRecords
{
    protected static string $resource = DailyReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->hidden(),
            Actions\Action::make('export')
                ->label('Export ke Excel')
                ->icon('heroicon-m-arrow-down-tray')
                ->action(function (HasTable $livewire) {
                    $reportDateFilter = $livewire->getTableFilterState('report_date') ?? [];
                    $startDate = $reportDateFilter['start_date'] ?? null;
                    $endDate = $reportDateFilter['end_date'] ?? null;

                    return Excel::download(
                        new DailyReportExport($startDate, $endDate),
                        'Riwayat_Transaksi_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
                    );
                }),
        ];
    }
}