<?php

namespace App\Filament\Merchant\Resources\ProductResource\Pages;

use App\Filament\Merchant\Resources\ProductResource;
use App\Filament\Merchant\Resources\ProductResource\Widgets\LowStockProductsOverview;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            LowStockProductsOverview::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }

    protected function getHeaderActions(): array
    {
        if (auth()->user()?->role === 'kasir') {
            return [];
        }

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