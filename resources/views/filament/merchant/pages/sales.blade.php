@php
    $products = $this->getProducts();
@endphp

<div class="space-y-6">
    <!-- Header -->
    <div class="fi-header flex flex-col justify-between gap-3 sm:flex-row sm:items-center">
        <h1 class="text-3xl font-bold tracking-tight text-gray-950 dark:text-white">
            Penjualan / Kasir
        </h1>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6" x-data="cartData()" x-cloak>
        <!-- Kolom Produk -->
        <div class="lg:col-span-2">
            <div
                class="rounded-xl border border-gray-950/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900">
                <div class="mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Daftar Produk</h2>
                    <input type="text"
                        wire:model.live="search"
                        class="w-full mb-4 rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-800 px-4 py-2"
                        placeholder="Cari produk berdasarkan nama atau barcode...">
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4" wire:key="products-grid-{{ $products->currentPage() }}-{{ md5($search) }}">
                    @if($products->isEmpty())
                        <div class="col-span-full text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">Tidak ada produk tersedia</p>
                        </div>
                    @else
                        @foreach($products as $product)
                            <div class="product-card border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:shadow-lg transition cursor-pointer dark:bg-gray-800"
                                data-product-id="{{ $product->id }}" 
                                data-product-name="{{ $product->name }}"
                                data-product-barcode="{{ $product->barcode ?? '' }}"
                                data-product-price="{{ $product->price }}" 
                                data-product-stock="{{ $product->stock }}">

                                @if($product->image)
                                    <div class="mb-3 h-44 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                            class="h-44 w-full object-cover">
                                    </div>
                                @else
                                    <div class="mb-3 h-44 overflow-hidden flex items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                        <img src="https://dummyimage.com/600x400/000/fff" alt="" class="h-44 w-full object-cover">
                                    </div>
                                @endif

                                <h3 class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 my-2">
                                    {{ $product->name }}
                                </h3>

                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ $product->category?->name }}
                                </p>

                                <p class="text-lg font-bold text-green-600 dark:text-green-400 mb-3">
                                    Rp {{ number_format($product->price, 0, ',', '.') }}
                                </p>

                                <div class="flex items-center justify-between mb-3">
                                    <span class="text-xs text-gray-600 dark:text-gray-400">
                                        Stok: <span
                                            class="font-semibold @if($product->stock <= $product->low_stock_threshold) text-red-600 @endif">
                                            {{ $product->stock }}
                                        </span>
                                    </span>
                                </div>

                                <button @click="addToCart({
                                                id: {{ $product->id }}, 
                                                name: '{{ addslashes($product->name) }}',
                                                price: {{ $product->price }},
                                                stock: {{ $product->stock }}})"
                                    :disabled="getProductStock({{ $product->id }}) <= 0"
                                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-400 text-white font-semibold py-2 px-3 rounded-lg transition text-sm">
                                    <span x-show="getProductStock({{ $product->id }}) <= 0">Stok Habis</span>
                                    <span x-show="getProductStock({{ $product->id }}) > 0">+ Keranjang</span>
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- Pagination -->
                <div class="mt-6" wire:key="products-pagination-{{ $products->currentPage() }}-{{ md5($search) }}">
                    @if ($products->hasPages())
                        <nav class="flex flex-col gap-4 rounded-2xl border border-gray-200 bg-gray-50/80 px-4 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-gray-800 dark:bg-gray-800/60">
                            <div class="text-sm text-gray-600 dark:text-gray-300">
                                Menampilkan {{ $products->firstItem() ?? 0 }} - {{ $products->lastItem() ?? 0 }} dari {{ $products->total() }} produk
                            </div>
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                @if ($products->onFirstPage())
                                    <button type="button" disabled class="rounded-xl border border-gray-200/80 bg-gray-100/80 px-4 py-2 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-500">&larr; Sebelumnya</button>
                                @else
                                    <button type="button" wire:click="previousPage('page')" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-200 dark:hover:border-sky-800 dark:hover:bg-sky-950/40 dark:hover:text-sky-300">&larr; Sebelumnya</button>
                                @endif

                                @foreach ($products->getUrlRange(1, $products->lastPage()) as $page => $url)
                                    @if ($page == $products->currentPage())
                                        <button type="button" class="min-w-10 rounded-xl border border-sky-200 bg-sky-100 px-3 py-2 text-sm font-semibold text-sky-700 shadow-sm dark:border-sky-900 dark:bg-sky-950/60 dark:text-sky-300">{{ $page }}</button>
                                    @else
                                        <button type="button" wire:click="gotoPage({{ $page }}, 'page')" class="min-w-10 rounded-xl border border-gray-200 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-200 dark:hover:border-sky-800 dark:hover:bg-sky-950/40 dark:hover:text-sky-300">{{ $page }}</button>
                                    @endif
                                @endforeach

                                @if ($products->hasMorePages())
                                    <button type="button" wire:click="nextPage('page')" class="rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm transition hover:border-sky-300 hover:bg-sky-50 hover:text-sky-700 dark:border-gray-700 dark:bg-gray-900/70 dark:text-gray-200 dark:hover:border-sky-800 dark:hover:bg-sky-950/40 dark:hover:text-sky-300">Selanjutnya &rarr;</button>
                                @else
                                    <button type="button" disabled class="rounded-xl border border-gray-200/80 bg-gray-100/80 px-4 py-2 text-sm font-medium text-gray-400 shadow-sm cursor-not-allowed dark:border-white/10 dark:bg-white/[0.04] dark:text-gray-500">Selanjutnya &rarr;</button>
                                @endif
                            </div>
                        </nav>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Keranjang -->
        <div class="lg:col-span-1">
            <div
                class="rounded-xl border border-gray-950/10 bg-white p-6 shadow-sm dark:border-white/10 dark:bg-gray-900 sticky top-20">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Keranjang</h2>

                <div class="space-y-3 mb-4 max-h-96 overflow-y-auto">
                    <template x-if="cart.length === 0">
                        <p class="text-gray-500 dark:text-gray-400 text-center py-8">Keranjang kosong</p>
                    </template>

                    <template x-for="item in cart" :key="item.id">
                        <div
                            class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-3">
                            <div class="flex-1">
                                <p class="font-semibold text-gray-900 dark:text-white text-sm" x-text="item.name"></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Rp <span x-text="formatNumber(item.price)"></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-1">
                                <button @click="decreaseQuantity(item.id)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 bg-gray-100 text-base font-semibold leading-none text-gray-700 transition hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                                    −
                                </button>
                                <input type="number" x-model.number="item.quantity" min="1" :max="item.stock"
                                    class="h-8 w-12 rounded-lg border border-gray-300 bg-white text-center text-sm font-semibold text-gray-900 [appearance:textfield] dark:border-gray-600 dark:bg-gray-800 dark:text-white [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none"
                                    @change="validateQuantity(item)">
                                <button @click="increaseQuantity(item.id, item.stock)"
                                    class="flex h-8 w-8 items-center justify-center rounded-lg border border-gray-300 bg-gray-100 text-base font-semibold leading-none text-gray-700 transition hover:bg-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                                    +
                                </button>
                                <button @click="removeFromCart(item.id)"
                                    class="ml-2 text-red-600 hover:text-red-800 dark:text-red-400 text-sm">Hapus</button>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700 pt-4 space-y-2">
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Subtotal:</span>
                        <span>Rp <span x-text="formatNumber(subtotal)"></span></span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
                        <span>Total Item:</span>
                        <span x-text="totalItems"></span>
                    </div>
                    <div
                        class="flex justify-between text-lg font-bold text-gray-900 dark:text-white pt-2 border-t border-gray-200 dark:border-gray-700">
                        <span>Total:</span>
                        <span>Rp <span x-text="formatNumber(total)"></span></span>
                    </div>
                </div>

                <button @click="checkout()" :disabled="cart.length === 0"
                    class="w-full mt-4 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-bold py-2 px-4 rounded-lg transition">
                    Lanjut Ke Pembayaran
                </button>

                <button :disabled="cart.length === 0" @click="clearCart()"
                    class="w-full mt-2 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white font-semibold py-2 px-4 rounded-lg transition">
                    Bersihkan Keranjang
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function cartData() {
        return {
            cart: [],

            init() {
                this.loadCart();
                this.$watch('cart', () => this.saveCart(), { deep: true });
            },

            loadCart() {
                const saved = localStorage.getItem('cart');
                this.cart = saved ? JSON.parse(saved) : [];
            },

            saveCart() {
                localStorage.setItem('cart', JSON.stringify(this.cart));
            },

            getProductStock(productId) {
                const cartItem = this.cart.find(item => item.id === productId);
                const usedStock = cartItem ? cartItem.quantity : 0;
                const productCard = document.querySelector(`[data-product-id="${productId}"]`);
                const totalStock = productCard ? parseInt(productCard.getAttribute('data-product-stock')) : 0;
                return totalStock - usedStock;
            },

            addToCart(product) {
                const existingItem = this.cart.find(item => item.id === product.id);

                if (existingItem) {
                    if (existingItem.quantity < product.stock) {
                        existingItem.quantity++;
                        this.showNotification('success', `${product.name} ditambahkan ke keranjang`);
                    } else {
                        this.showNotification('warning', 'Stok produk tidak mencukupi');
                    }
                } else {
                    this.cart.push({
                        id: product.id,
                        name: product.name,
                        price: product.price,
                        stock: product.stock,
                        quantity: 1
                    });
                    this.showNotification('success', `${product.name} ditambahkan ke keranjang`);
                }
            },

            removeFromCart(productId) {
                const item = this.cart.find(item => item.id === productId);
                const name = item ? item.name : 'Produk';
                this.cart = this.cart.filter(item => item.id !== productId);
                this.showNotification('info', `${name} dihapus dari keranjang`);
            },

            increaseQuantity(productId, maxStock) {
                const item = this.cart.find(item => item.id === productId);
                if (item && item.quantity < maxStock) {
                    item.quantity++;
                } else if (item && item.quantity >= maxStock) {
                    this.showNotification('warning', 'Stok tidak mencukupi');
                }
            },

            decreaseQuantity(productId) {
                const item = this.cart.find(item => item.id === productId);
                if (item && item.quantity > 1) {
                    item.quantity--;
                } else if (item && item.quantity === 1) {
                    this.removeFromCart(productId);
                }
            },

            clearCart() {
                if (confirm('Apakah Anda yakin ingin menghapus semua item dari keranjang?')) {
                    this.cart = [];
                    this.showNotification('info', 'Keranjang telah dikosongkan');
                }
            },

            checkout() {
                if (this.cart.length === 0) {
                    this.showNotification('warning', 'Keranjang masih kosong');
                    return;
                }

                // TODO: Implementasikan halaman checkout
                alert('Fitur checkout baru akan hadir segera!');
                // window.location.href = '/merchant/checkout';
            },

            validateQuantity(item) {
                if (item.quantity < 1) {
                    item.quantity = 1;
                } else if (item.quantity > item.stock) {
                    item.quantity = item.stock;
                    this.showNotification('warning', 'Jumlah tidak boleh melebihi stok');
                }
            },

            showNotification(type, message) {
                // Implementasi toast/notification
                console.log(`[${type.toUpperCase()}] ${message}`);
            },

            get subtotal() {
                return this.cart.reduce((total, item) => total + (item.price * item.quantity), 0);
            },

            get total() {
                return this.subtotal;
            },

            get totalItems() {
                return this.cart.reduce((count, item) => count + item.quantity, 0);
            },

            formatNumber(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            }
        };
    }
</script>