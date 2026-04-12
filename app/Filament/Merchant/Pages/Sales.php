<?php

namespace App\Filament\Merchant\Pages;

use App\Models\Product;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Livewire\WithPagination;

class Sales extends Page
{
    use WithPagination;

    protected const PRODUCTS_PER_PAGE = 6;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationLabel = 'Penjualan';
    protected static ?int $navigationSort = 1;
    protected static string $view = 'filament.merchant.pages.sales';

    public $search = '';
    protected $queryString = [
        'search' => ['except' => ''],
    ];
    protected $paginationTheme = 'tailwind';

    public function getTitle(): string | Htmlable
    {
        return 'Penjualan / Kasir';
    }

    public function updatingSearch()
    {
        $this->resetPage('page');
    }

    public function updatedSearch()
    {
        $this->resetPage('page');
    }

    public function getProducts()
    {
        $merchantId = filament()->getTenant()?->id;

        if (!$merchantId) {
            return collect();
        }

        $query = Product::where('merchant_id', $merchantId)
            ->with('category');

        if ($this->search) {
            $searchTerm = '%' . $this->search . '%';
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', $searchTerm)
                  ->orWhere('barcode', 'like', $searchTerm);
            });
        }

        $products = (clone $query)
            ->orderBy('name')
            ->paginate(static::PRODUCTS_PER_PAGE);

        if ($products->total() > 0 && $products->currentPage() > $products->lastPage()) {
            $this->resetPage('page');

            $products = $query
                ->orderBy('name')
                ->paginate(static::PRODUCTS_PER_PAGE, ['*'], 'page', 1);
        }

        return $products;
    }
}
