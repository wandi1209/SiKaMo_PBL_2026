<?php

namespace App\Livewire\Merchant;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\TransactionRefundService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesPage extends Component
{
    use WithPagination;

    protected const PRODUCTS_PER_PAGE = 12;

    public $search = '';
    public $category = 'all';
    public $categories = [];
    protected $paginationTheme = 'tailwind';

    // Cart properties
    public $cart = [];
    public $total = 0;

    // Tab switching
    public $activeTab = 'produk';

    public function mount()
    {
        $this->loadCategories();
    }

    public function setTab($tab)
    {
        if (in_array($tab, ['produk', 'keranjang', 'scan', 'pembayaran', 'struk'])) {
            $this->activeTab = $tab;
        }
    }

    public function updatingSearch()
    {
        $this->resetPage('page');
    }

    public function updatedSearch()
    {
        $this->resetPage('page');
    }

    public function updatedCategory()
    {
        $this->resetPage('page');
    }

    public function selectCategory($categoryId)
    {
        $this->category = $categoryId;
    }

    private function loadCategories(): void
    {
        $merchantId = $this->getMerchantId();

        if (! $merchantId) {
            $this->categories = [];
            return;
        }

        $this->categories = Category::whereHas('products', function ($query) use ($merchantId) {
            $query->where('merchant_id', $merchantId);
        })->orderBy('name')->get();
    }

    private function getMerchantId(): ?int
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if ($user->merchant_id) {
            return $user->merchant_id;
        }

        if ($user->merchant?->id) {
            return $user->merchant->id;
        }

        return filament()?->getTenant()?->id;
    }

    public function getProducts()
    {
        $merchantId = $this->getMerchantId();

        if (!$merchantId) {
            return Product::query()
                ->whereNull('id')
                ->paginate(static::PRODUCTS_PER_PAGE);
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

        if ($this->category !== 'all') {
            $query->where('category_id', $this->category);
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

    public function getLowStockProducts()
    {
        $merchantId = $this->getMerchantId();

        if (! $merchantId) {
            return collect();
        }

        return Product::query()
            ->with('category')
            ->where('merchant_id', $merchantId)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(6)
            ->get();
    }

    public function getLowStockProductsCount(): int
    {
        $merchantId = $this->getMerchantId();

        if (! $merchantId) {
            return 0;
        }

        return Product::query()
            ->where('merchant_id', $merchantId)
            ->whereColumn('stock', '<=', 'low_stock_threshold')
            ->count();
    }

    public function addToCart($productId)
    {
        $merchantId = $this->getMerchantId();

        if (!$merchantId) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Merchant tidak ditemukan']);
            return;
        }

        $product = Product::where('id', $productId)
            ->where('merchant_id', $merchantId)
            ->first();

        if (!$product) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Produk tidak ditemukan']);
            return;
        }

        if ($product->stock <= 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Stok produk habis']);
            return;
        }

        // Check if product already in cart
        $index = collect($this->cart)->search(fn($item) => $item['id'] == $productId);

        if ($index !== false) {
            if ($this->cart[$index]['quantity'] >= $product->stock) {
                $this->dispatch('showToast', type: 'error', message: 'Stok tidak mencukupi');
                return;
            }

            $this->cart[$index]['quantity']++;
        } else {
            $this->cart[] = [
                'id' => $product->id,
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => 1,
            ];
        }

        $this->calculateTotal();
        $this->dispatch('showToast', ['type' => 'success', 'message' => 'Produk ditambahkan ke keranjang']);
    }

    public function addByBarcode($barcode)
    {
        $barcode = trim((string) $barcode);

        if ($barcode === '') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Barcode tidak boleh kosong']);
            return;
        }

        $merchantId = $this->getMerchantId();

        if (!$merchantId) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Merchant tidak ditemukan']);
            return;
        }

        $product = Product::where('barcode', $barcode)
            ->where('merchant_id', $merchantId)
            ->first();

        if (!$product) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => "Produk dengan barcode {$barcode} tidak ditemukan"]);
            return;
        }

        if ($product->stock <= 0) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => "Stok produk {$product->name} habis"]);
            return;
        }

        $this->addToCart($product->id);
    }

    public function removeFromCart($productId)
    {
        $this->cart = collect($this->cart)->filter(function ($item) use ($productId) {
            return $item['id'] != $productId;
        })->values()->all();

        $this->calculateTotal();
    }

    public function updateQuantity($productId, $quantity)
    {
        if ($quantity <= 0) {
            $this->removeFromCart($productId);
            return;
        }

        $merchantId = $this->getMerchantId();
        $product = Product::where('id', $productId)
            ->where('merchant_id', $merchantId)
            ->first();

        if (!$product) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Produk tidak ditemukan']);
            return;
        }

        if ($quantity > $product->stock) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Stok tidak mencukupi']);
            return;
        }

        $index = collect($this->cart)->search(fn($item) => $item['id'] == $productId);
        if ($index !== false) {
            $this->cart[$index]['quantity'] = $quantity;
            $this->calculateTotal();
        }
    }

    public function calculateTotal()
    {
        $this->total = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });
    }

    public function checkout($paymentMethod = 'cash', $amountPaid = null)
    {
        Log::info('[Checkout] method called', ['paymentMethod' => $paymentMethod, 'amountPaid' => $amountPaid, 'cartCount' => count($this->cart)]);

        if (!in_array($paymentMethod, ['cash', 'qris'])) {
            $paymentMethod = 'cash';
        }

        if (empty($this->cart)) {
            Log::warning('[Checkout] cart is empty');
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Keranjang kosong']);
            return;
        }

        $user = Auth::user();
        $merchantId = $this->getMerchantId();

        Log::info('[Checkout] auth check', ['userId' => $user?->id, 'merchantId' => $merchantId]);

        if (!$user || !$merchantId) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'User tidak terautentikasi']);
            return;
        }

        Log::info('[Checkout] starting DB transaction');
        try {
            $transaction = DB::transaction(function () use ($user, $merchantId, $paymentMethod, $amountPaid) {
                $totalAmount = 0;
                $transactionItems = [];

                foreach ($this->cart as $item) {
                    $product = Product::find($item['id']);

                    if (!$product) {
                        throw new \Exception("Produk dengan ID {$item['id']} tidak ditemukan");
                    }

                    if ($product->merchant_id !== $merchantId) {
                        throw new \Exception("Produk {$product->name} tidak termasuk dalam merchant Anda");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}");
                    }

                    $subtotal = $item['price'] * $item['quantity'];
                    $totalAmount += $subtotal;

                    $transactionItems[] = [
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'subtotal' => $subtotal,
                        'product' => $product,
                    ];
                }

                // Hitung jumlah bayar & kembalian
                if ($paymentMethod === 'cash') {
                    $paid = (float) ($amountPaid ?? $totalAmount);
                    if ($paid < $totalAmount) {
                        throw new \Exception("Jumlah pembayaran kurang dari total belanja.");
                    }
                    $change = $paid - $totalAmount;
                } else {
                    $paid = $totalAmount;
                    $change = 0;
                }

                $invoiceNumber = $this->generateInvoiceNumber();

                $transaction = Transaction::create([
                    'user_id' => $user->id,
                    'invoice_number' => $invoiceNumber,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $paid,
                    'change_amount' => $change,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                ]);

                foreach ($transactionItems as $itemData) {
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'subtotal' => $itemData['subtotal'],
                    ]);

                    $product = $itemData['product'];
                    app()->instance('audit_context', [
                        'system_actor' => true,
                        'system_reason' => 'sale',
                        'transaction_id' => $transaction->id,
                        'transaction_invoice_number' => $transaction->invoice_number,
                        'triggered_by_user_id' => $user->id,
                        'triggered_by_user_name' => $user->name,
                        'triggered_by_user_role' => $user->role,
                    ]);

                    try {
                        $product->decrement('stock', $itemData['quantity']);
                    } finally {
                        app()->forgetInstance('audit_context');
                    }
                }

                return $transaction;
            });

            Log::info('[Checkout] transaction complete', ['invoiceNumber' => $transaction->invoice_number]);

            $receiptItems = collect($this->cart)->map(fn($item) => [
                'name' => $item['name'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ])->values()->all();

            $this->dispatch('checkoutSuccess', [
                'invoiceNumber' => $transaction->invoice_number,
                'total' => $transaction->total_amount,
                'amountPaid' => $transaction->amount_paid,
                'change' => $transaction->change_amount,
                'paymentMethod' => $transaction->payment_method,
                'createdAt' => $transaction->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                'merchantName' => $user->merchant?->name ?? 'Toko Kelontong',
                'merchantAddress' => $user->merchant?->address ?? 'SiKaMo POS',
                'cashierName' => $user->name,
                'items' => $receiptItems,
            ]);

            $this->cart = [];
            $this->total = 0;

            $this->dispatch('showToast', [
                'type' => 'success',
                'message' => 'Transaksi berhasil!'
            ]);
        } catch (\Throwable $e) {
            Log::error('[Checkout] FAILED: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            $this->dispatch('checkoutError', [
                'message' => $e->getMessage()
            ]);
        }
    }

    public function refundTransaction(string $invoiceNumber, TransactionRefundService $refundService): bool
    {
        $user = Auth::user();
        $merchantId = $this->getMerchantId();

        if (! $user || ! $merchantId) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'User tidak terautentikasi']);

            return false;
        }

        $transaction = Transaction::query()
            ->where('invoice_number', $invoiceNumber)
            ->whereHas('items.product', fn($query) => $query->where('merchant_id', $merchantId))
            ->first();

        if (! $transaction) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Transaksi tidak ditemukan']);

            return false;
        }

        try {
            $refundService->refund($transaction, $user, 'Refund dari halaman struk kasir');

            $this->dispatch('showToast', ['type' => 'success', 'message' => 'Transaksi berhasil direfund']);

            return true;
        } catch (\Throwable $e) {
            Log::error('[Refund] FAILED: ' . $e->getMessage(), ['invoiceNumber' => $invoiceNumber]);
            $this->dispatch('showToast', ['type' => 'error', 'message' => $e->getMessage()]);

            return false;
        }
    }

    private function generateInvoiceNumber()
    {
        $date = now()->format('Ymd');
        $latestTransaction = Transaction::whereDate('created_at', now())
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $latestTransaction ? intval(substr($latestTransaction->invoice_number, -4)) + 1 : 1;
        $sequence = str_pad($sequence, 4, '0', STR_PAD_LEFT);

        return "INV-{$date}-{$sequence}";
    }

    public function render()
    {
        return view('livewire.merchant.sales-page', [
            'products' => $this->getProducts(),
            'lowStockProducts' => $this->getLowStockProducts(),
            'lowStockProductsCount' => $this->getLowStockProductsCount(),
        ])->layout('components.layouts.transaction', [
            'title' => 'Transaksi Penjualan',
        ]);
    }
}
