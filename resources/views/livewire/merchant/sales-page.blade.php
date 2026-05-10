<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800" x-data="checkoutData()" x-cloak>
    @if (session()->has('success') || session()->has('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="rounded-xl p-4 shadow-sm text-sm {{ session()->has('success') ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-red-50 text-red-800 border border-red-200' }}">
                {{ session()->get(session()->has('success') ? 'success' : 'error') }}
            </div>
        </div>
    @endif

    <!-- Header dengan tombol kembali -->
    <div class="sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="@php
                    $merchant = auth()->user()?->merchant;
                    echo $merchant ? route('filament.merchant.pages.dashboard', ['tenant' => $merchant->id]) : '#';
                @endphp" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Kembali ke Dashboard
                </a>
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white">Transaksi Penjualan</h1>
            </div>
            <div class="flex items-center gap-4">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Toko: <span class="font-medium">{{ auth()->user()?->merchant?->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Product List -->
            <div class="lg:col-span-2">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Produk</h2>
                        <div class="flex items-center gap-2">
                            <input wire:model.live.debounce.300ms="search" type="text"
                                   placeholder="Cari produk..."
                                   class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                        @foreach($products as $product)
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 border border-gray-200 dark:border-gray-600 hover:shadow-md transition-shadow">
                                <div class="aspect-square bg-gray-200 dark:bg-gray-600 rounded-lg mb-3 flex items-center justify-center">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
                                        </svg>
                                    @endif
                                </div>
                                <h3 class="font-medium text-gray-900 dark:text-white text-sm mb-1">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-600 dark:text-gray-400 mb-2">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Stok: {{ $product->stock }}</span>
                                </div>
                                <button wire:click="addToCart({{ $product->id }})"
                                        @if($product->stock <= 0) disabled @endif
                                        class="w-full mt-3 px-3 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white text-xs font-medium rounded-lg transition-colors">
                                    @if($product->stock <= 0)
                                        Stok Habis
                                    @else
                                        Tambah ke Keranjang
                                    @endif
                                </button>
                            </div>
                        @endforeach
                    </div>

                    @if($products->hasPages())
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Cart -->
            <div class="lg:col-span-1">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 sticky top-24">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Keranjang Belanja</h2>

                    @if(count($cart) > 0)
                        <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                            @foreach($cart as $item)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900 dark:text-white text-sm">{{ $item['name'] }}</h4>
                                        <p class="text-xs text-gray-600 dark:text-gray-400">Rp {{ number_format($item['price'], 0, ',', '.') }} × {{ $item['quantity'] }}</p>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                class="w-6 h-6 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded text-xs font-medium">-</button>
                                        <span class="text-sm font-medium w-8 text-center">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                class="w-6 h-6 bg-gray-200 hover:bg-gray-300 dark:bg-gray-600 dark:hover:bg-gray-500 rounded text-xs font-medium">+</button>
                                    </div>
                                    <button wire:click="removeFromCart({{ $item['id'] }})"
                                            class="text-red-500 hover:text-red-700 ml-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                            <div class="flex justify-between items-center mb-4">
                                <span class="font-semibold text-gray-900 dark:text-white">Total:</span>
                                <span class="font-bold text-lg text-green-600 dark:text-green-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
                            </div>

                            <button @click="cartTotal = $wire.total; showPaymentModal = true; paymentStep = 1"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-4 rounded-lg transition-colors">
                                Bayar Sekarang
                            </button>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
                            </svg>
                            <p class="text-gray-500 dark:text-gray-400">Keranjang kosong</p>
                            <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Tambahkan produk untuk memulai transaksi</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Step 1: Pilih Metode -->
    <div x-show="showPaymentModal && paymentStep === 1" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50" @click="showPaymentModal = false"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Pilih Metode Pembayaran</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <button @click="selectedMethod = 'cash'; paymentStep = 2"
                        class="flex flex-col items-center justify-center p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-green-500 hover:bg-green-50 dark:hover:bg-green-900/20 transition-all">
                    <svg class="w-9 h-9 text-green-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Tunai (Cash)</span>
                </button>
                <button @click="selectedMethod = 'qris'; paymentStep = 2"
                        class="flex flex-col items-center justify-center p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-blue-500 hover:bg-blue-50 dark:hover:bg-blue-900/20 transition-all">
                    <svg class="w-9 h-9 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">QRIS</span>
                </button>
            </div>
            <button @click="showPaymentModal = false; resetPayment()"
                    class="w-full py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                Batal
            </button>
        </div>
    </div>

    <!-- Payment Step 2a: Tunai (Cash) -->
    <div x-show="showPaymentModal && paymentStep === 2 && selectedMethod === 'cash'" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Pembayaran Tunai</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Total: <span class="font-bold text-green-600 dark:text-green-400" x-text="'Rp ' + formatCurrency(cartTotal)"></span>
            </p>

            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Jumlah Uang Diterima</label>
            <input type="number" x-model="amountPaid" @input="calcChange()" min="0"
                   placeholder="Masukkan jumlah bayar..."
                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-green-500 focus:border-transparent mb-3">

            <div class="grid grid-cols-3 gap-2 mb-4">
                <template x-for="nominal in quickNominals" :key="nominal">
                    <button @click="amountPaid = nominal; calcChange()"
                            class="py-1.5 text-xs rounded-lg border border-gray-200 dark:border-gray-600 hover:bg-green-50 dark:hover:bg-green-900/20 hover:border-green-400 transition-all dark:text-gray-300"
                            x-text="'Rp ' + formatCurrency(nominal)"></button>
                </template>
            </div>

            <div x-show="Number(amountPaid) >= cartTotal && cartTotal > 0"
                 class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-700 rounded-lg p-3 mb-4">
                <p class="text-sm text-blue-700 dark:text-blue-300">
                    Kembalian: <span class="font-bold text-lg" x-text="'Rp ' + formatCurrency(change)"></span>
                </p>
            </div>
            <div x-show="Number(amountPaid) > 0 && Number(amountPaid) < cartTotal"
                 class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3 mb-4">
                <p class="text-sm text-red-700 dark:text-red-300">
                    Kurang: <span class="font-bold" x-text="'Rp ' + formatCurrency(cartTotal - Number(amountPaid))"></span>
                </p>
            </div>

            <div class="flex gap-3">
                <button @click="paymentStep = 1; amountPaid = ''"
                        class="flex-1 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Kembali
                </button>
                <button @click="confirmCash()"
                        :disabled="!amountPaid || Number(amountPaid) < cartTotal"
                        class="flex-1 py-2 rounded-lg bg-green-600 hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium transition-colors">
                    Konfirmasi
                </button>
            </div>
        </div>
    </div>

    <!-- Payment Step 2b: QRIS -->
    <div x-show="showPaymentModal && paymentStep === 2 && selectedMethod === 'qris'" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-sm p-6 text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Bayar dengan QRIS</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Total: <span class="font-bold text-blue-600 dark:text-blue-400" x-text="'Rp ' + formatCurrency(cartTotal)"></span>
            </p>
            <div class="flex justify-center mb-4">
                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=SIKAMO-QRIS-' + cartTotal"
                     alt="QR Code QRIS" class="rounded-lg border-4 border-blue-100 shadow w-48 h-48">
            </div>
            <p class="text-xs text-gray-400 mb-5">Scan QR Code di atas menggunakan aplikasi pembayaran Anda</p>
            <div class="flex gap-3">
                <button @click="paymentStep = 1"
                        class="flex-1 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Kembali
                </button>
                <button @click="confirmQris()"
                        class="flex-1 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
                    Sudah Bayar
                </button>
            </div>
        </div>
    </div>

    <!-- Checkout Success Modal -->
    <div x-show="showCheckoutModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center px-4"
         style="display: none;">
        <div class="absolute inset-0 bg-gray-900/50" @click="closeModal()"></div>
        <div class="relative bg-white dark:bg-gray-800 rounded-xl shadow-xl w-full max-w-md p-6">
            <div class="flex items-start gap-4 mb-4">
                <div class="flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-green-100">
                    <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Transaksi Berhasil!</h3>
                    <div class="mt-2 space-y-1">
                        <p class="text-sm text-gray-500 dark:text-gray-400">Invoice: <span class="font-mono font-medium" x-text="invoiceNumber"></span></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Total: <span class="font-semibold" x-text="'Rp ' + formatCurrency(totalAmount)"></span></p>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Metode: <span class="font-semibold uppercase" x-text="receiptPaymentMethod"></span></p>
                        <template x-if="receiptPaymentMethod === 'cash'">
                            <div>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Bayar: <span class="font-semibold" x-text="'Rp ' + formatCurrency(receiptAmountPaid)"></span></p>
                                <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold">Kembalian: <span x-text="'Rp ' + formatCurrency(receiptChange)"></span></p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
            <div class="flex gap-3 justify-end">
                <button @click="closeModal()"
                        class="px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-sm">
                    Tutup
                </button>
                <button @click="printReceipt()"
                        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium">
                    Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function checkoutData() {
        return {
            // Payment modal state
            showPaymentModal: false,
            paymentStep: 1,
            selectedMethod: '',
            amountPaid: '',
            change: 0,
            cartTotal: {{ $total }},
            quickNominals: [5000, 10000, 20000, 50000, 100000, 200000],

            // Success modal state
            showCheckoutModal: false,
            invoiceNumber: '',
            totalAmount: 0,
            receiptAmountPaid: 0,
            receiptChange: 0,
            receiptPaymentMethod: '',

            init() {
                window.addEventListener('checkoutSuccess', (event) => {
                    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    this.invoiceNumber = data?.invoiceNumber || '';
                    this.totalAmount = data?.total || 0;
                    this.receiptAmountPaid = data?.amountPaid || 0;
                    this.receiptChange = data?.change || 0;
                    this.receiptPaymentMethod = data?.paymentMethod || '';
                    this.showPaymentModal = false;
                    this.showCheckoutModal = true;
                    this.resetPayment();
                });

                window.addEventListener('checkoutError', (event) => {
                    const data = Array.isArray(event.detail) ? event.detail[0] : event.detail;
                    this.showPaymentModal = false;
                    this.resetPayment();
                    alert(data?.message || 'Transaksi gagal. Silakan coba lagi.');
                });
            },

            calcChange() {
                const paid = parseFloat(this.amountPaid) || 0;
                this.change = paid >= this.cartTotal ? paid - this.cartTotal : 0;
            },

            confirmCash() {
                const paid = parseFloat(this.amountPaid);
                if (!paid || paid < this.cartTotal) return;
                this.$wire.checkout('cash', paid);
            },

            confirmQris() {
                this.$wire.checkout('qris', this.cartTotal);
            },

            resetPayment() {
                this.paymentStep = 1;
                this.selectedMethod = '';
                this.amountPaid = '';
                this.change = 0;
            },

            formatCurrency(amount) {
                return new Intl.NumberFormat('id-ID').format(amount || 0);
            },

            closeModal() {
                this.showCheckoutModal = false;
                this.invoiceNumber = '';
                this.totalAmount = 0;
                this.receiptAmountPaid = 0;
                this.receiptChange = 0;
                this.receiptPaymentMethod = '';
            },

            printReceipt() {
                window.print();
            }
        };
    }
</script>
@endpush