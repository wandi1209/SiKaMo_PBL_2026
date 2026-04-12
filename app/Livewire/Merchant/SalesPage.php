<?php

namespace App\Livewire\Merchant;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class SalesPage extends Component
{
    use WithPagination;

    protected const PRODUCTS_PER_PAGE = 6;

    public $search = '';
    protected $paginationTheme = 'tailwind';

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
        // Dapatkan merchant_id dari user yang sedang login OR dari tenant Filament
        $merchantId = auth()->user()?->merchant_id ?? filament()?->getTenant()?->id;

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

    public function render()
    {
        return view('livewire.merchant.sales-page', [
            'products' => $this->getProducts(),
        ]);
    }
}