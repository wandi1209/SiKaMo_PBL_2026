<?php

namespace App\Livewire\Merchant;

use App\Events\ProductStockUpdated;
use App\Models\Merchant;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Category;
use App\Models\Discount;
use App\Models\Member;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Services\QrisPayloadService;
use App\Services\TransactionRefundService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesPage extends Component
{
    use WithPagination;

    protected const PRODUCTS_PER_PAGE = 12;
    protected const MOBILE_PRODUCTS_PER_PAGE = 8;

    public $search = '';
    public $category = 'all';
    public $categories = [];
    protected $paginationTheme = 'tailwind';

    // Cart properties
    public $cart = [];
    public $subtotal = 0;
    public $total = 0;
    public $discountCode = '';
    public $discountAmount = 0;
    public $appliedDiscount = null;
    public $memberPhone = '';
    public $selectedMember = null;
    public $memberDiscountAmount = 0;

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

    private function getMerchant(): ?Merchant
    {
        $merchantId = $this->getMerchantId();

        if (! $merchantId) {
            return null;
        }

        return Merchant::find($merchantId);
    }

    private function getQrisQrCodeUrl(): ?string
    {
        $payload = $this->getDynamicQrisPayload();

        if (! $payload) {
            return null;
        }

        return 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data=' . urlencode($payload);
    }

    private function getDynamicQrisPayload(): ?string
    {
        $payload = $this->getMerchant()?->qris_payload;

        if (! $payload || $this->total <= 0) {
            return null;
        }

        return app(QrisPayloadService::class)->withAmount($payload, $this->total);
    }

    public function getProducts()
    {
        $merchantId = $this->getMerchantId();

        if (!$merchantId) {
            return Product::query()
                ->whereNull('id')
                ->paginate($this->getProductsPerPage());
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

        $perPage = $this->getProductsPerPage();

        $products = (clone $query)
            ->orderBy('name')
            ->paginate($perPage);

        if ($products->total() > 0 && $products->currentPage() > $products->lastPage()) {
            $this->resetPage('page');

            $products = $query
                ->orderBy('name')
                ->paginate($perPage, ['*'], 'page', 1);
        }

        return $products;
    }

    private function getProductsPerPage(): int
    {
        $userAgent = request()->userAgent() ?? '';

        if (preg_match('/Android|webOS|iPhone|iPod|BlackBerry|IEMobile|Opera Mini/i', $userAgent)) {
            return static::MOBILE_PRODUCTS_PER_PAGE;
        }

        return static::PRODUCTS_PER_PAGE;
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
        $this->subtotal = collect($this->cart)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        $this->discountAmount = 0;

        if ($this->appliedDiscount) {
            $discount = Discount::query()
                ->where('merchant_id', $this->getMerchantId())
                ->whereKey($this->appliedDiscount['id'])
                ->first();

            if ($discount && $discount->isUsableFor((float) $this->subtotal)) {
                $this->discountAmount = $discount->calculateDiscount((float) $this->subtotal);
                $this->appliedDiscount = $this->discountPayload($discount);
            } else {
                $this->appliedDiscount = null;
                $this->discountCode = '';
            }
        }

        $this->memberDiscountAmount = $this->calculateMemberDiscountAmount(
            $this->getMerchant(),
            (float) $this->subtotal,
            max(0, (float) $this->subtotal - (float) $this->discountAmount),
        );

        $this->total = max(0, $this->subtotal - $this->discountAmount - $this->memberDiscountAmount);
    }

    public function applyDiscount(): void
    {
        $code = strtoupper(trim((string) $this->discountCode));

        if ($code === '') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Kode diskon tidak boleh kosong']);
            return;
        }

        if (empty($this->cart)) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Keranjang masih kosong']);
            return;
        }

        $discount = Discount::query()
            ->where('merchant_id', $this->getMerchantId())
            ->where('code', $code)
            ->first();

        if (! $discount || ! $discount->isUsableFor((float) $this->subtotal)) {
            $this->appliedDiscount = null;
            $this->discountAmount = 0;
            $this->calculateTotal();
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Kode diskon tidak valid atau belum memenuhi syarat']);
            return;
        }

        $this->discountCode = $code;
        $this->appliedDiscount = $this->discountPayload($discount);
        $this->calculateTotal();

        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => 'Diskon berhasil diterapkan',
        ]);
    }

    public function removeDiscount(): void
    {
        $this->discountCode = '';
        $this->discountAmount = 0;
        $this->appliedDiscount = null;
        $this->calculateTotal();
    }

    public function findMember(): void
    {
        $phone = $this->normalizePhone($this->memberPhone);

        if ($phone === '') {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Nomor HP member tidak boleh kosong']);
            return;
        }

        $member = Member::query()
            ->where('merchant_id', $this->getMerchantId())
            ->where('phone', $phone)
            ->where('is_active', true)
            ->first();

        if (! $member) {
            $this->selectedMember = null;
            $this->memberDiscountAmount = 0;
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Member tidak ditemukan atau tidak aktif']);
            $this->calculateTotal();
            return;
        }

        $this->memberPhone = $member->phone;
        $this->selectedMember = $this->memberPayload($member);
        $this->calculateTotal();

        $this->dispatch('showToast', [
            'type' => 'success',
            'message' => "Member {$member->name} diterapkan",
        ]);
    }

    public function removeMember(): void
    {
        $this->memberPhone = '';
        $this->selectedMember = null;
        $this->memberDiscountAmount = 0;
        $this->calculateTotal();
    }

    private function discountPayload(Discount $discount): array
    {
        return [
            'id' => $discount->id,
            'code' => $discount->code,
            'name' => $discount->name,
            'type' => $discount->type,
            'value' => (float) $discount->value,
            'maxDiscountAmount' => $discount->max_discount_amount !== null ? (float) $discount->max_discount_amount : null,
            'minPurchaseAmount' => (float) $discount->min_purchase_amount,
        ];
    }

    private function memberPayload(Member $member): array
    {
        return [
            'id' => $member->id,
            'name' => $member->name,
            'phone' => $member->phone,
        ];
    }

    private function normalizePhone(?string $phone): string
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);

        if (str_starts_with($phone, '+62')) {
            return '0' . substr($phone, 3);
        }

        if (str_starts_with($phone, '62')) {
            return '0' . substr($phone, 2);
        }

        return $phone;
    }

    private function calculateMemberDiscountAmount(?Merchant $merchant, float $subtotal, float $discountableAmount): float
    {
        if (! $this->selectedMember || ! $merchant?->member_discount_is_active) {
            return 0;
        }

        if ($subtotal < (float) $merchant->member_discount_min_purchase) {
            return 0;
        }

        $discountableAmount = max(0, $discountableAmount);

        if ($discountableAmount <= 0) {
            return 0;
        }

        if ($merchant->member_discount_type === 'percent') {
            $discount = $discountableAmount * ((float) $merchant->member_discount_value / 100);

            if ($merchant->member_discount_max_amount !== null) {
                $discount = min($discount, (float) $merchant->member_discount_max_amount);
            }

            return min($discount, $discountableAmount);
        }

        return min((float) $merchant->member_discount_value, $discountableAmount);
    }

    public function checkout($paymentMethod = 'cash', $amountPaid = null)
    {
        Log::info('[Checkout] method called', ['paymentMethod' => $paymentMethod, 'amountPaid' => $amountPaid, 'cartCount' => count($this->cart)]);

        if (!in_array($paymentMethod, ['cash', 'qris'])) {
            $paymentMethod = 'cash';
        }

        if ($paymentMethod === 'qris' && ! $this->getDynamicQrisPayload()) {
            $this->dispatch('showToast', ['type' => 'error', 'message' => 'Payload QRIS toko belum dikonfigurasi']);
            return;
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
                $subtotalAmount = 0;
                $transactionItems = [];

                foreach ($this->cart as $item) {
                    $product = Product::query()
                        ->where('merchant_id', $merchantId)
                        ->lockForUpdate()
                        ->find($item['id']);

                    if (!$product) {
                        throw new \Exception("Produk dengan ID {$item['id']} tidak ditemukan");
                    }

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi. Stok tersedia: {$product->stock}");
                    }

                    $subtotal = $item['price'] * $item['quantity'];
                    $unitCost = (float) ($product->cost_price ?? 0);
                    $totalCost = $unitCost * $item['quantity'];
                    $subtotalAmount += $subtotal;

                    $transactionItems[] = [
                        'product_id' => $item['id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['price'],
                        'unit_cost' => $unitCost,
                        'subtotal' => $subtotal,
                        'total_cost' => $totalCost,
                        'gross_profit' => $subtotal - $totalCost,
                        'product' => $product,
                    ];
                }

                $discount = null;
                $voucherDiscountAmount = 0;

                if ($this->appliedDiscount) {
                    $discount = Discount::query()
                        ->where('merchant_id', $merchantId)
                        ->whereKey($this->appliedDiscount['id'])
                        ->lockForUpdate()
                        ->first();

                    if (! $discount || ! $discount->isUsableFor((float) $subtotalAmount)) {
                        throw new \Exception('Kode diskon tidak valid atau sudah tidak bisa digunakan.');
                    }

                    $voucherDiscountAmount = $discount->calculateDiscount((float) $subtotalAmount);
                }

                $member = null;
                $memberDiscountAmount = 0;

                if ($this->selectedMember) {
                    $member = Member::query()
                        ->where('merchant_id', $merchantId)
                        ->whereKey($this->selectedMember['id'])
                        ->where('is_active', true)
                        ->lockForUpdate()
                        ->first();

                    if (! $member) {
                        throw new \Exception('Member tidak ditemukan atau sudah tidak aktif.');
                    }

                    $merchant = Merchant::query()
                        ->whereKey($merchantId)
                        ->lockForUpdate()
                        ->first();

                    $this->selectedMember = $this->memberPayload($member);
                    $memberDiscountAmount = $this->calculateMemberDiscountAmount(
                        $merchant,
                        (float) $subtotalAmount,
                        max(0, (float) $subtotalAmount - (float) $voucherDiscountAmount),
                    );
                }

                $totalDiscountAmount = $voucherDiscountAmount + $memberDiscountAmount;
                $totalAmount = max(0, $subtotalAmount - $totalDiscountAmount);

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
                    'discount_id' => $discount?->id,
                    'discount_code' => $discount?->code,
                    'member_id' => $member?->id,
                    'member_name' => $member?->name,
                    'member_phone' => $member?->phone,
                    'invoice_number' => $invoiceNumber,
                    'subtotal_amount' => $subtotalAmount,
                    'discount_amount' => $totalDiscountAmount,
                    'member_discount_amount' => $memberDiscountAmount,
                    'total_amount' => $totalAmount,
                    'amount_paid' => $paid,
                    'change_amount' => $change,
                    'payment_method' => $paymentMethod,
                    'status' => 'completed',
                ]);

                if ($discount) {
                    $discount->increment('used_count');
                }

                $allocatedDiscount = 0;
                $lastIndex = count($transactionItems) - 1;

                foreach ($transactionItems as $index => $itemData) {
                    $itemDiscount = 0;

                    if ($totalDiscountAmount > 0 && $subtotalAmount > 0) {
                        $itemDiscount = $index === $lastIndex
                            ? $totalDiscountAmount - $allocatedDiscount
                            : round($totalDiscountAmount * ($itemData['subtotal'] / $subtotalAmount), 2);

                        $allocatedDiscount += $itemDiscount;
                    }

                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
                        'product_id' => $itemData['product_id'],
                        'quantity' => $itemData['quantity'],
                        'unit_price' => $itemData['unit_price'],
                        'unit_cost' => $itemData['unit_cost'],
                        'subtotal' => $itemData['subtotal'],
                        'total_cost' => $itemData['total_cost'],
                        'gross_profit' => $itemData['subtotal'] - $itemDiscount - $itemData['total_cost'],
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
                        $product->refresh();

                        DB::afterCommit(fn() => event(ProductStockUpdated::fromProduct($product)));
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

            $merchant = $this->getMerchant();

            $this->dispatch('checkoutSuccess', [
                'invoiceNumber' => $transaction->invoice_number,
                'subtotal' => $transaction->subtotal_amount,
                'discountCode' => $transaction->discount_code,
                'discountAmount' => max(0, (float) $transaction->discount_amount - (float) $transaction->member_discount_amount),
                'memberName' => $transaction->member_name,
                'memberPhone' => $transaction->member_phone,
                'memberDiscountAmount' => $transaction->member_discount_amount,
                'total' => $transaction->total_amount,
                'amountPaid' => $transaction->amount_paid,
                'change' => $transaction->change_amount,
                'paymentMethod' => $transaction->payment_method,
                'createdAt' => $transaction->created_at?->timezone('Asia/Jakarta')->format('d M Y, H:i'),
                'merchantName' => $merchant?->name ?? 'Toko Kelontong',
                'merchantAddress' => $merchant?->address ?? 'SiKaMo POS',
                'cashierName' => $user->name,
                'items' => $receiptItems,
            ]);

            $this->cart = [];
            $this->subtotal = 0;
            $this->total = 0;
            $this->discountCode = '';
            $this->discountAmount = 0;
            $this->appliedDiscount = null;
            $this->memberPhone = '';
            $this->selectedMember = null;
            $this->memberDiscountAmount = 0;

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
            'qrisQrCodeUrl' => $this->getQrisQrCodeUrl(),
            'merchantRealtimeChannel' => $this->getMerchantId() ? 'merchant.' . $this->getMerchantId() : null,
        ])->layout('components.layouts.transaction', [
            'title' => 'Transaksi Penjualan',
        ]);
    }
}
