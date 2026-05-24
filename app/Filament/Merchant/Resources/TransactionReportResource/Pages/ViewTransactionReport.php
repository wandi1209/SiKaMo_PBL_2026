<?php

namespace App\Filament\Merchant\Resources\TransactionReportResource\Pages;

use App\Filament\Merchant\Resources\TransactionReportResource;
use App\Services\TransactionRefundService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
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
            Actions\Action::make('refund')
                ->label('Refund')
                ->icon('heroicon-o-arrow-uturn-left')
                ->color('danger')
                ->visible(fn(): bool => $this->record->status === 'completed')
                ->requiresConfirmation()
                ->modalHeading('Refund transaksi ini?')
                ->modalDescription('Stok produk akan dikembalikan dan transaksi akan ditandai sebagai refund.')
                ->form([
                    Forms\Components\Textarea::make('reason')
                        ->label('Alasan refund')
                        ->rows(3)
                        ->maxLength(1000),
                ])
                ->action(function (array $data, TransactionRefundService $refundService): void {
                    $refundService->refund($this->record, auth()->user(), $data['reason'] ?? null);

                    Notification::make()
                        ->title('Transaksi berhasil direfund')
                        ->body('Stok produk sudah dikembalikan.')
                        ->success()
                        ->send();

                    $this->redirect(TransactionReportResource::getUrl('index'));
                }),

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
