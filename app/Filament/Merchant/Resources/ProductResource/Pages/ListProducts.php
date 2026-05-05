<?php

namespace App\Filament\Merchant\Resources\ProductResource\Pages;

use App\Filament\Merchant\Resources\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New Produk'),

            Action::make('scanBarcode')
                ->label('Scan Barcode')
                ->icon('heroicon-o-qr-code')
                ->color('gray')
                ->modalHeading('Scan Barcode Produk')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn () => view('filament.merchant.resources.product-resource.actions.scan-barcode', [
                    'createUrl' => ProductResource::getUrl('create'),
                ])),
        ];
    }
}
