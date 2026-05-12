<div class="min-h-screen bg-gray-100 dark:bg-gray-950" x-data="checkoutData()" x-init="init()" x-cloak
     @keydown.escape.window="mobileCartOpen = false">


    <!-- Header dengan tombol kembali -->
    <div class="sticky top-0 z-50 bg-white/90 backdrop-blur-sm dark:bg-gray-900/95 border-b border-gray-200 dark:border-gray-700/50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="@php
                    $merchant = auth()->user()?->merchant;
                    echo $merchant ? route('filament.merchant.pages.dashboard', ['tenant' => $merchant->id]) : '#';
                @endphp" class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    <span class="hidden sm:inline">Kembali ke Dashboard</span>
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
                <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Produk</h2>
                        <div class="flex items-center gap-2">
                            <input wire:model.live.debounce.300ms="search" type="text"
                                   placeholder="Cari produk..."
                                   class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-transparent dark:bg-gray-700 dark:text-white">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($products as $product)
                            @if($product->stock > 0)
                            {{-- Card produk tersedia: melayang saat hover, bisa diklik --}}
                            <button type="button" wire:key="product-{{ $product->id }}" wire:click.prevent="addToCart({{ $product->id }})" wire:loading.attr="disabled"
                                 class="relative cursor-pointer bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700/50 shadow-sm hover:shadow-2xl hover:shadow-emerald-100 dark:hover:shadow-emerald-900/20 hover:-translate-y-2 hover:border-emerald-300 dark:hover:border-emerald-600 transition-all duration-300 group text-left w-full">
                                <div class="aspect-square bg-gray-100 dark:bg-gray-600 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-lg group-hover:scale-105 transition-transform duration-300">
                                    @else
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm mb-1 truncate">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-bold text-green-600 dark:text-green-400">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <span class="text-xs font-medium text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 px-2 py-0.5 rounded-full">Stok: {{ $product->stock }}</span>
                                </div>
                            </button>
                            @else
                            {{-- Card stok habis: melayang tapi redup & tidak bisa diklik --}}
                            <div class="relative bg-white dark:bg-gray-800 rounded-xl p-4 border border-red-200 dark:border-red-900/30 shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-300 opacity-60 grayscale cursor-not-allowed">
                                {{-- Overlay merah tipis --}}
                                <div class="absolute inset-0 bg-red-50/40 dark:bg-red-900/20 rounded-xl pointer-events-none z-10"></div>
                                <div class="aspect-square bg-gray-100 dark:bg-gray-600 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    @if($product->image)
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" class="w-full h-full object-cover rounded-lg">
                                    @else
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V8a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    @endif
                                </div>
                                <h3 class="font-semibold text-gray-500 dark:text-gray-400 text-sm mb-1 truncate">{{ $product->name }}</h3>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mb-2">{{ $product->category?->name ?? 'Uncategorized' }}</p>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-bold text-gray-400 dark:text-gray-500">Rp {{ number_format($product->price, 0, ',', '.') }}</span>
                                    <span class="text-xs font-medium text-red-500 dark:text-red-400 bg-red-50 dark:bg-red-900/30 px-2 py-0.5 rounded-full">Stok: 0</span>
                                </div>
                                <div class="w-full px-3 py-1.5 bg-red-500/80 text-white text-xs font-semibold rounded-lg text-center tracking-wide">
                                    🚫 Stok Habis
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    @if($products->hasPages())
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Desktop Cart Sidebar (sticky, hidden on mobile) -->
            <div class="hidden lg:block">
                <div class="sticky top-24">
                    <div class="bg-white dark:bg-gray-900 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-800 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
                                </svg>
                                <h2 class="text-lg font-semibold text-white">Keranjang</h2>
                                @if(count($cart) > 0)
                                    <span class="ml-auto bg-white/20 text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ count($cart) }} item</span>
                                @endif
                            </div>
                        </div>

                        <div class="p-5">
                            @if(count($cart) > 0)
                                <div class="space-y-3 mb-4 max-h-[calc(100vh-22rem)] overflow-y-auto pr-1 scrollbar-thin">
                                    @foreach($cart as $item)
                                        <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600/50 hover:border-emerald-200 dark:hover:border-emerald-700 transition-colors">
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $item['name'] }}</h4>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rp {{ number_format($item['price'], 0, ',', '.') }} × {{ $item['quantity'] }}</p>
                                            </div>
                                            <div class="flex items-center gap-1.5">
                                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                        class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-red-100 hover:text-red-600 dark:bg-gray-600 dark:hover:bg-red-900/40 rounded-lg text-xs font-bold transition-colors">−</button>
                                                <span class="text-sm font-semibold w-7 text-center text-gray-800 dark:text-gray-200">{{ $item['quantity'] }}</span>
                                                <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                        class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-emerald-100 hover:text-emerald-600 dark:bg-gray-600 dark:hover:bg-emerald-900/40 rounded-lg text-xs font-bold transition-colors">+</button>
                                            </div>
                                            <button wire:click="removeFromCart({{ $item['id'] }})"
                                                    class="text-gray-400 hover:text-red-500 transition-colors ml-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <span class="font-semibold text-gray-900 dark:text-white">Total:</span>
                                        <span class="font-bold text-xl text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                    </div>
                                    <button @click="cartTotal = $wire.total; showPaymentModal = true; paymentStep = 1"
                                            class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-emerald-200 dark:shadow-emerald-900/30 hover:shadow-xl hover:shadow-emerald-300 dark:hover:shadow-emerald-900/50 active:scale-[0.98]">
                                        💳 Bayar Sekarang
                                    </button>
                                </div>
                            @else
                                <div class="text-center py-10">
                                    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-500 dark:text-gray-400 font-medium">Keranjang kosong</p>
                                    <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Klik produk untuk menambahkan</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile: FAB Cart Button -->
    <button @click="mobileCartOpen = !mobileCartOpen"
            class="lg:hidden fixed bottom-6 right-6 z-40 w-16 h-16 bg-gradient-to-br from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-full shadow-2xl shadow-emerald-500/30 flex items-center justify-center transition-all active:scale-90 hover:scale-105">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
        </svg>
        @if(count($cart) > 0)
            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold w-6 h-6 flex items-center justify-center rounded-full shadow ring-2 ring-white dark:ring-gray-900 animate-bounce">{{ count($cart) }}</span>
        @endif
    </button>

    <!-- Mobile: Cart Slide-up Panel -->
    <div x-show="mobileCartOpen" x-cloak class="lg:hidden fixed inset-0 z-50" style="display:none;">
        <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="mobileCartOpen = false" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>
        <div class="absolute bottom-0 left-0 right-0 bg-white dark:bg-gray-900 rounded-t-3xl shadow-2xl max-h-[85vh] overflow-hidden"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-y-0" x-transition:leave-end="translate-y-full">
            <!-- Handle bar -->
            <div class="flex justify-center pt-3 pb-1">
                <div class="w-10 h-1.5 bg-gray-300 dark:bg-gray-600 rounded-full"></div>
            </div>
            <div class="px-5 pb-2 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
                    </svg>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Keranjang</h2>
                    @if(count($cart) > 0)
                        <span class="bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300 text-xs font-bold px-2 py-0.5 rounded-full">{{ count($cart) }}</span>
                    @endif
                </div>
                <button @click="mobileCartOpen = false" class="p-2 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <div class="px-5 pb-6 overflow-y-auto max-h-[calc(85vh-8rem)]">
                @if(count($cart) > 0)
                    <div class="space-y-3 mb-4">
                        @foreach($cart as $item)
                            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-gray-700/50 rounded-xl border border-gray-100 dark:border-gray-600/50">
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-medium text-gray-900 dark:text-white text-sm truncate">{{ $item['name'] }}</h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Rp {{ number_format($item['price'], 0, ',', '.') }} × {{ $item['quantity'] }}</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                            class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-red-100 dark:bg-gray-600 rounded-lg text-xs font-bold transition-colors">−</button>
                                    <span class="text-sm font-semibold w-7 text-center">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                            class="w-7 h-7 flex items-center justify-center bg-gray-200 hover:bg-emerald-100 dark:bg-gray-600 rounded-lg text-xs font-bold transition-colors">+</button>
                                </div>
                                <button wire:click="removeFromCart({{ $item['id'] }})" class="text-gray-400 hover:text-red-500 transition-colors ml-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-gray-200 dark:border-gray-600 pt-4">
                        <div class="flex justify-between items-center mb-4">
                            <span class="font-semibold text-gray-900 dark:text-white">Total:</span>
                            <span class="font-bold text-xl text-emerald-600 dark:text-emerald-400">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <button @click="mobileCartOpen = false; cartTotal = $wire.total; showPaymentModal = true; paymentStep = 1"
                                class="w-full bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-semibold py-3.5 px-4 rounded-xl transition-all shadow-lg shadow-emerald-200 dark:shadow-emerald-900/30 active:scale-[0.98]">
                            💳 Bayar Sekarang
                        </button>
                    </div>
                @else
                    <div class="text-center py-10">
                        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-700 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 dark:text-gray-400 font-medium">Keranjang kosong</p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-1">Klik produk untuk menambahkan</p>
                    </div>
                @endif
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
                        class="flex flex-col items-center justify-center p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all">
                    <svg class="w-9 h-9 text-emerald-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                    <span class="font-medium text-gray-900 dark:text-white">Tunai (Cash)</span>
                </button>
                <button @click="selectedMethod = 'qris'; paymentStep = 2"
                        class="flex flex-col items-center justify-center p-5 border-2 border-gray-200 dark:border-gray-700 rounded-xl hover:border-teal-500 hover:bg-teal-50 dark:hover:bg-teal-900/20 transition-all">
                    <svg class="w-9 h-9 text-teal-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                 class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-700 rounded-lg p-3 mb-4">
                <p class="text-sm text-emerald-700 dark:text-emerald-300">
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
                        class="flex-1 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-400 disabled:cursor-not-allowed text-white font-medium transition-colors">
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
        <div class="relative bg-white dark:bg-gray-900 rounded-xl shadow-xl w-full max-w-sm p-6 text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1">Bayar dengan QRIS</h3>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                Total: <span class="font-bold text-teal-600 dark:text-teal-400" x-text="'Rp ' + formatCurrency(cartTotal)"></span>
            </p>
            <div class="flex justify-center mb-4">
                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=SIKAMO-QRIS-' + cartTotal"
                     alt="QR Code QRIS" class="rounded-lg border-4 border-teal-100 dark:border-teal-800 shadow w-48 h-48">
            </div>
            <p class="text-xs text-gray-400 mb-5">Scan QR Code di atas menggunakan aplikasi pembayaran Anda</p>
            <div class="flex gap-3">
                <button @click="paymentStep = 1"
                        class="flex-1 py-2 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                    Kembali
                </button>
                <button @click="confirmQris()"
                        class="flex-1 py-2 rounded-lg bg-teal-600 hover:bg-teal-700 text-white font-medium transition-colors">
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
                                <p class="text-sm text-emerald-600 dark:text-emerald-400 font-semibold">Kembalian: <span x-text="'Rp ' + formatCurrency(receiptChange)"></span></p>
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
                        class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">
                    Cetak Struk
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Register Livewire → window bridge ONCE (guard prevents duplicate on re-render)
    if (!window.__sikamoListenersRegistered) {
        window.__sikamoListenersRegistered = true;

        document.addEventListener('livewire:init', function () {
            Livewire.on('showToast', function (data) {
                const d = Array.isArray(data) ? data[0] : data;
                const msg = d?.message || 'Notifikasi';
                const type = d?.type || 'info';
                if (window.toastr && toastr[type]) toastr[type](msg);
            });

            Livewire.on('checkoutSuccess', function (data) {
                const d = Array.isArray(data) ? data[0] : data;
                window.dispatchEvent(new CustomEvent('sikamo:checkoutSuccess', { detail: d }));
            });

            Livewire.on('checkoutError', function (data) {
                const d = Array.isArray(data) ? data[0] : data;
                const msg = d?.message || 'Transaksi gagal. Silakan coba lagi.';
                if (window.toastr) toastr.error(msg);
                window.dispatchEvent(new CustomEvent('sikamo:checkoutError', { detail: d }));
            });
        });
    }

    function checkoutData() {
        return {
            // Mobile cart toggle
            mobileCartOpen: false,

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
                // Listen to native window events (bridged from Livewire once, never duplicated)
                window.addEventListener('sikamo:checkoutSuccess', (e) => {
                    const d = e.detail;
                    this.invoiceNumber = d.invoiceNumber || '';
                    this.totalAmount = d.total || 0;
                    this.receiptAmountPaid = d.amountPaid || 0;
                    this.receiptChange = d.change || 0;
                    this.receiptPaymentMethod = d.paymentMethod || '';
                    this.showPaymentModal = false;
                    this.showCheckoutModal = true;
                    this.resetPayment();
                });

                window.addEventListener('sikamo:checkoutError', (e) => {
                    this.showPaymentModal = false;
                    this.resetPayment();
                });
            },

            calcChange() {
                const paid = parseFloat(this.amountPaid) || 0;
                this.change = paid >= this.cartTotal ? paid - this.cartTotal : 0;
            },

            confirmCash() {
                const paid = parseFloat(this.amountPaid);
                console.log('💰 confirmCash() called - paid:', paid, 'cartTotal:', this.cartTotal);
                if (!paid || paid < this.cartTotal) {
                    console.warn('❌ Payment invalid or insufficient');
                    return;
                }
                console.log('✅ Calling checkout method with cash payment');
                this.$wire.call('checkout', 'cash', paid);
            },

            confirmQris() {
                console.log('📱 confirmQris() called - cartTotal:', this.cartTotal);
                console.log('✅ Calling checkout method with qris payment');
                this.$wire.call('checkout', 'qris', this.cartTotal);
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