<?php

namespace App\Filament\Merchant\Resources\TransactionReportResource\Pages;

use App\Filament\Merchant\Resources\TransactionReportResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTransactionReport extends ViewRecord
{
    protected static string $resource = TransactionReportResource::class;

    protected static string $view = 'filament.merchant.resources.transaction-report-resource.pages.view-transaction-report';

    public function getTitle(): string
    {
        return 'Detail Penjualan';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('print')
                ->label('Cetak')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->extraAttributes([
                    'x-on:click' => 'window.print()',
                ]),

            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(TransactionReportResource::getUrl('index')),
        ];
    }
}
