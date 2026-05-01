<?php

namespace App\Filament\Merchant\Resources\DailyReportResource\Pages;

use App\Filament\Merchant\Resources\DailyReportResource;
use App\Exports\DailyReportExport;
use Filament\Resources\Pages\ListRecords;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
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
                ->form([
                    DatePicker::make('start_date')
                        ->label('Dari Tanggal')
                        ->default(null),
                    DatePicker::make('end_date')
                        ->label('Sampai Tanggal')
                        ->default(null),
                ])
                ->action(function (array $data) {
                    $startDate = $data['start_date'] ? date('Y-m-d', strtotime($data['start_date'])) : null;
                    $endDate = $data['end_date'] ? date('Y-m-d', strtotime($data['end_date'])) : null;

                    return Excel::download(
                        new DailyReportExport($startDate, $endDate),
                        'Riwayat_Transaksi_' . now()->format('Y-m-d_H-i-s') . '.xlsx'
                    );
                }),
        ];
    }
}
