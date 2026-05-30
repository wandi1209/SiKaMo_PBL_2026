<div class="h-screen bg-gray-50 dark:bg-gray-950 flex flex-col font-sans overflow-hidden" x-data="checkoutData()"
    x-init="init()" x-cloak>

    <!-- Top Navbar: Desktop only — Premium Glassmorphism Design -->
    @php
        $tenantId = auth()->user()?->role === 'owner'
            ? auth()->user()?->merchant?->id
            : auth()->user()?->employedAt?->id;
        $dashboardUrl = $tenantId ? url("/merchant/toko/{$tenantId}") : url('/merchant');
        $merchantName = auth()->user()?->merchant?->name
            ?? auth()->user()?->employedAt?->name
            ?? 'Toko Kelontong';
        $userInitial = strtoupper(substr(auth()->user()?->name ?? 'A', 0, 1));
        $userName    = auth()->user()?->name ?? 'Admin Toko';
    @endphp
    <header class="w-full shrink-0 hidden md:flex items-center justify-between px-6 z-40 sticky top-0 h-[68px]
                   bg-white/80 dark:bg-gray-900/80 backdrop-blur-xl
                   border-b border-white/60 dark:border-gray-800/60
                   shadow-[0_1px_16px_rgba(0,0,0,0.06)] dark:shadow-[0_1px_16px_rgba(0,0,0,0.3)]">

        <!-- LEFT: Store Badge -->
        <div class="flex items-center gap-3 min-w-0">
            <!-- Store Icon with gradient ring -->
            <div class="relative shrink-0">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-emerald-400 to-emerald-700
                            flex items-center justify-center shadow-lg shadow-emerald-500/30 dark:shadow-emerald-700/30">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </div>
                <!-- Online dot indicator -->
                <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-emerald-400 border-2 border-white dark:border-gray-900 rounded-full shadow-sm"></span>
            </div>
            <div class="min-w-0">
                <h2 class="font-extrabold text-gray-900 dark:text-white text-sm tracking-tight leading-tight truncate max-w-[160px]">
                    {{ $merchantName }}
                </h2>
                <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-600 dark:text-emerald-400">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    Kasir Aktif
                </span>
            </div>
        </div>

        <!-- CENTER: Premium Tab Switcher -->
        <nav class="absolute left-1/2 -translate-x-1/2 flex items-center">
            <!-- Tab Container with pill track -->
            <div class="flex items-center bg-gray-100/80 dark:bg-gray-800/60 rounded-2xl p-1 gap-1 shadow-inner">

                <!-- Produk Tab -->
                <button type="button" @click="setTab('produk')"
                    class="relative flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold transition-all duration-300 cursor-pointer select-none"
                    :class="activeTab === 'produk'
                        ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 dark:shadow-emerald-700/40 scale-[1.02]'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                    </svg>
                    <span>Produk</span>
                </button>

                <!-- Keranjang Tab -->
                <button type="button" @click="setTab('keranjang')"
                    class="relative flex items-center gap-2 px-5 py-2 rounded-xl text-xs font-bold transition-all duration-300 cursor-pointer select-none"
                    :class="activeTab === 'keranjang'
                        ? 'bg-gradient-to-r from-emerald-500 to-emerald-600 text-white shadow-lg shadow-emerald-500/30 dark:shadow-emerald-700/40 scale-[1.02]'
                        : 'text-gray-500 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-white/60 dark:hover:bg-gray-700/50'">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03" />
                    </svg>
                    <span>Keranjang</span>
                    @if (count($cart) > 0)
                        <span class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full
                                     text-[9px] font-black leading-none
                                     shadow-sm"
                              :class="activeTab === 'keranjang'
                                  ? 'bg-white/30 text-white ring-1 ring-white/40'
                                  : 'bg-emerald-500 text-white'">
                            {{ count($cart) }}
                        </span>
                    @endif
                </button>

            </div>
        </nav>

        <!-- RIGHT: Profile Button + Dropdown -->
        <div class="relative shrink-0" x-data="{ openProfileMenu: false }">
                <button type="button" @click="openProfileMenu = !openProfileMenu"
                    class="flex items-center gap-2.5 px-3 py-2 rounded-2xl cursor-pointer
                           bg-gray-50 dark:bg-gray-800/60 hover:bg-gray-100 dark:hover:bg-gray-800
                           border border-gray-200/80 dark:border-gray-700/60
                           shadow-sm hover:shadow-md
                           transition-all duration-200 focus:outline-none">
                    <!-- Avatar with gradient ring -->
                    <div class="relative shrink-0">
                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600
                                    text-white flex items-center justify-center font-black text-xs
                                    shadow-md shadow-emerald-500/20 ring-2 ring-emerald-500/20">
                            {{ $userInitial }}
                        </div>
                    </div>
                    <div class="min-w-0 hidden lg:flex flex-col items-start">
                        <span class="text-xs font-bold text-gray-900 dark:text-white leading-tight truncate max-w-[96px]">{{ $userName }}</span>
                        <span class="text-[10px] text-gray-400 dark:text-gray-500 font-medium">{{ auth()->user()?->role === 'owner' ? 'Pemilik' : 'Kasir' }}</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 transition-transform duration-200 shrink-0"
                        :class="openProfileMenu ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <!-- Dropdown (opens downwards, right-aligned) -->
                <div x-show="openProfileMenu"
                    @click.away="openProfileMenu = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                    class="absolute top-full right-0 mt-2.5 w-64
                           bg-white/90 dark:bg-gray-900/90 backdrop-blur-xl
                           border border-gray-200/60 dark:border-gray-700/60
                           rounded-2xl shadow-2xl shadow-black/10 dark:shadow-black/40
                           p-2 z-50 space-y-0.5"
                    style="display: none;"
                    x-data="{
                        activeTheme: localStorage.getItem('theme') || 'system',
                        setTheme(newTheme) {
                            this.activeTheme = newTheme;
                            localStorage.setItem('theme', newTheme);
                            if (newTheme === 'dark' || (newTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                        }
                    }">

                    <!-- User Profile Header -->
                    <div class="flex items-center gap-3 p-3 mb-1 rounded-xl bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-950/30 dark:to-teal-950/20 border border-emerald-100/60 dark:border-emerald-900/40">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center font-black text-sm shadow-md shadow-emerald-500/20 shrink-0">
                            {{ $userInitial }}
                        </div>
                        <div class="min-w-0">
                            <span class="block text-sm font-bold text-gray-900 dark:text-white truncate">{{ $userName }}</span>
                            <span class="block text-[10px] font-semibold text-emerald-600 dark:text-emerald-450">
                                {{ auth()->user()?->role === 'owner' ? '🏪 Pemilik Toko' : '🧾 Kasir' }}
                            </span>
                        </div>
                    </div>

                    <!-- Divider label -->
                    <p class="px-3 pt-1 pb-0.5 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600">Tampilan</p>

                    <!-- Theme Switcher Row -->
                    <div class="flex items-center gap-1 p-1.5 rounded-xl bg-gray-100/80 dark:bg-gray-800/60 mx-1">
                        <!-- Light Button -->
                        <button type="button" @click="setTheme('light')"
                            title="Mode Terang"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            :class="activeTheme === 'light'
                                ? 'bg-white dark:bg-gray-700 shadow text-amber-500 ring-1 ring-amber-200 dark:ring-amber-700/50'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                            <span>Terang</span>
                        </button>
                        <!-- Dark Button -->
                        <button type="button" @click="setTheme('dark')"
                            title="Mode Gelap"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            :class="activeTheme === 'dark'
                                ? 'bg-white dark:bg-gray-700 shadow text-indigo-500 ring-1 ring-indigo-200 dark:ring-indigo-700/50'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.718 9.718 0 0118 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 003 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 009.002-5.998z" />
                            </svg>
                            <span>Gelap</span>
                        </button>
                        <!-- System Button -->
                        <button type="button" @click="setTheme('system')"
                            title="Ikuti Sistem"
                            class="flex-1 flex items-center justify-center gap-1.5 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            :class="activeTheme === 'system'
                                ? 'bg-white dark:bg-gray-700 shadow text-emerald-600 ring-1 ring-emerald-200 dark:ring-emerald-700/50'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25M3 5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25z" />
                            </svg>
                            <span>Auto</span>
                        </button>
                    </div>

                    <!-- Divider label for Text Size -->
                    <p class="px-3 pt-2 pb-0.5 text-[10px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-600">Ukuran Teks</p>

                    <!-- Text Size Switcher Row -->
                    <div class="flex items-center gap-1 p-1.5 rounded-xl bg-gray-100/80 dark:bg-gray-800/60 mx-1">
                        <!-- Kecil Button -->
                        <button type="button" @click="setTextSize('small')"
                            title="Teks Kecil"
                            class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            :class="textSize === 'small'
                                ? 'bg-white dark:bg-gray-700 shadow text-blue-500 ring-1 ring-blue-200 dark:ring-blue-700/50'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <span class="text-[10px] font-black">A</span>
                            <span>Kecil</span>
                        </button>
                        <!-- Sedang Button -->
                        <button type="button" @click="setTextSize('medium')"
                            title="Teks Sedang"
                            class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            :class="textSize === 'medium'
                                ? 'bg-white dark:bg-gray-700 shadow text-emerald-500 ring-1 ring-emerald-200 dark:ring-emerald-700/50'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <span class="text-[13px] font-black">A</span>
                            <span>Sedang</span>
                        </button>
                        <!-- Besar Button -->
                        <button type="button" @click="setTextSize('large')"
                            title="Teks Besar"
                            class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg text-xs font-bold transition-all duration-200 cursor-pointer"
                            :class="textSize === 'large'
                                ? 'bg-white dark:bg-gray-700 shadow text-orange-500 ring-1 ring-orange-200/80 dark:ring-orange-700/50'
                                : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200'">
                            <span class="text-[17px] leading-none font-black">A</span>
                            <span>Besar</span>
                        </button>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-gray-100 dark:border-gray-800/60 mx-2 my-1"></div>

                    <!-- Kembali ke Dashboard -->
                    <a href="{{ $dashboardUrl }}"
                        class="flex items-center gap-3 px-3 py-2.5 text-xs font-semibold text-gray-700 dark:text-gray-200
                               hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-700 dark:hover:text-emerald-400
                               rounded-xl transition-all duration-200 cursor-pointer group">
                        <div class="w-7 h-7 rounded-lg bg-gray-100 dark:bg-gray-800 group-hover:bg-emerald-100 dark:group-hover:bg-emerald-900/40
                                    flex items-center justify-center transition-colors duration-200">
                            <svg class="w-3.5 h-3.5 text-gray-500 dark:text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-450 transition-colors"
                                 fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                            </svg>
                        </div>
                        <span>Kembali ke Dashboard</span>
                    </a>
                </div>
            </div>
    </header>

    <!-- Mobile Top Navbar -->
    <header class="md:hidden sticky top-0 z-40 w-full shrink-0 border-b border-gray-100 bg-white/95 px-3 py-2.5 shadow-sm backdrop-blur dark:border-gray-800 dark:bg-gray-900/95">
        <div class="flex items-center justify-between gap-3">
            <button type="button"
                @click="activeTab === 'pembayaran' ? setTab('keranjang') : (activeTab === 'scan' ? setTab('produk') : setTab('produk'))"
                class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl border border-gray-100 bg-gray-50 text-gray-600 shadow-sm dark:border-gray-800 dark:bg-gray-950 dark:text-gray-300"
                x-show="activeTab !== 'produk'">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
            </button>

            <div class="min-w-0 flex-1" :class="activeTab === 'produk' ? '' : 'text-center'">
                <p class="truncate text-sm font-black text-gray-950 dark:text-white"
                    x-text="activeTab === 'produk' ? @js($merchantName) : (activeTab === 'keranjang' ? 'Keranjang' : (activeTab === 'scan' ? 'Scan Produk' : (activeTab === 'pembayaran' ? 'Pembayaran' : 'Struk')))">
                    {{ $merchantName }}
                </p>
                <p class="truncate text-[10px] font-bold text-emerald-600 dark:text-emerald-400"
                    x-text="activeTab === 'produk' ? 'Kasir Aktif' : 'SiKaMo POS'">Kasir Aktif</p>
            </div>

            <div class="relative shrink-0" x-data="{
                openMobileProfileMenu: false,
                activeTheme: localStorage.getItem('theme') || 'system',
                setTheme(newTheme) {
                    this.activeTheme = newTheme;
                    localStorage.setItem('theme', newTheme);
                    if (newTheme === 'dark' || (newTheme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                }
            }">
                <button type="button" @click="openMobileProfileMenu = !openMobileProfileMenu"
                    class="flex h-10 max-w-[168px] items-center gap-2 rounded-2xl border border-gray-200 bg-gray-50 px-2.5 shadow-sm transition dark:border-gray-700 dark:bg-gray-800/80">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-xs font-black text-white shadow-sm">
                        {{ $userInitial }}
                    </span>
                    <span class="min-w-0 flex-1 text-left">
                        <span class="block truncate text-xs font-black leading-tight text-gray-950 dark:text-white">{{ $userName }}</span>
                        <span class="block truncate text-[10px] font-bold leading-tight text-gray-400 dark:text-gray-500">
                            {{ auth()->user()?->role === 'owner' ? 'Pemilik' : 'Kasir' }}
                        </span>
                    </span>
                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400 transition-transform" :class="openMobileProfileMenu ? 'rotate-180' : ''"
                        fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>

                <div x-show="openMobileProfileMenu"
                    @click.away="openMobileProfileMenu = false"
                    x-transition:enter="transition ease-out duration-150"
                    x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-100"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                    class="absolute right-0 top-full mt-2 w-[min(270px,calc(100vw-1.5rem))] overflow-hidden rounded-2xl border border-gray-200 bg-white/95 p-2 text-left shadow-2xl shadow-gray-950/15 backdrop-blur-xl dark:border-gray-700 dark:bg-gray-900/95 dark:shadow-black/40"
                    style="display: none;">
                    <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-emerald-50/80 p-3 dark:border-emerald-900/40 dark:bg-emerald-950/20">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-500 text-sm font-black text-white shadow-md">
                            {{ $userInitial }}
                        </span>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-black text-gray-950 dark:text-white">{{ $userName }}</p>
                            <p class="truncate text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                {{ auth()->user()?->role === 'owner' ? 'Pemilik Toko' : 'Kasir' }}
                            </p>
                        </div>
                    </div>

                    <p class="px-3 pt-3 pb-1 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-600">Tampilan</p>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800/70">
                        <button type="button" @click="setTheme('light')"
                            class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] font-black transition"
                            :class="activeTheme === 'light' ? 'bg-white text-amber-600 shadow dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.36-6.36-.7.7M6.34 17.66l-.7.7m0-12.72.7.7m12.72 12.72.7.7M12 8a4 4 0 100 8 4 4 0 000-8z" />
                            </svg>
                            Terang
                        </button>
                        <button type="button" @click="setTheme('dark')"
                            class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] font-black transition"
                            :class="activeTheme === 'dark' ? 'bg-white text-indigo-600 shadow dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 15A9.75 9.75 0 119 2.25 7.5 7.5 0 0021.75 15z" />
                            </svg>
                            Gelap
                        </button>
                        <button type="button" @click="setTheme('system')"
                            class="flex items-center justify-center gap-1.5 rounded-lg px-2 py-2 text-[11px] font-black transition"
                            :class="activeTheme === 'system' ? 'bg-white text-emerald-600 shadow dark:bg-gray-700' : 'text-gray-500 dark:text-gray-400'">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.01A3 3 0 018.12 20.38L7.5 21h9l-.62-.62A3 3 0 0115 18.26v-1.01M3 5.25A2.25 2.25 0 015.25 3h13.5A2.25 2.25 0 0121 5.25V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25z" />
                            </svg>
                            Auto
                        </button>
                    </div>

                    <p class="px-3 pt-3 pb-1 text-[10px] font-black uppercase tracking-widest text-gray-400 dark:text-gray-600">Ukuran Teks</p>
                    <div class="grid grid-cols-3 gap-1 rounded-xl bg-gray-100 p-1 dark:bg-gray-800/70">
                        <button type="button" @click="setTextSize('small')"
                            class="rounded-lg px-2 py-2 text-[11px] font-black transition"
                            :class="textSize === 'small' ? 'bg-blue-600 text-white shadow' : 'text-gray-500 dark:text-gray-400'">A Kecil</button>
                        <button type="button" @click="setTextSize('medium')"
                            class="rounded-lg px-2 py-2 text-[11px] font-black transition"
                            :class="textSize === 'medium' ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white' : 'text-gray-500 dark:text-gray-400'">A Sedang</button>
                        <button type="button" @click="setTextSize('large')"
                            class="rounded-lg px-2 py-2 text-[11px] font-black transition"
                            :class="textSize === 'large' ? 'bg-white text-gray-900 shadow dark:bg-gray-700 dark:text-white' : 'text-gray-500 dark:text-gray-400'">A Besar</button>
                    </div>

                    <a href="{{ $dashboardUrl }}"
                        class="mt-2 flex items-center gap-3 rounded-xl px-3 py-3 text-xs font-black text-gray-700 transition hover:bg-gray-50 dark:text-gray-200 dark:hover:bg-gray-800">
                        <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l9-9 9 9M4.5 9.75V21h5.25v-6h4.5v6h5.25V9.75" />
                            </svg>
                        </span>
                        Kembali ke Dashboard
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Mobile Bottom Navigation: Fixed on mobile -->
    <nav
        class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 z-40 grid grid-cols-2 py-2.5 shadow-lg">
        <button type="button" @click="setTab('produk')"
            class="flex flex-col items-center justify-center transition-all cursor-pointer"
            :class="activeTab === 'produk' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            <span class="text-[10px] font-semibold mt-1">Produk</span>
        </button>
        <button type="button" @click="setTab('keranjang')"
            class="flex flex-col items-center justify-center transition-all cursor-pointer relative"
            :class="activeTab === 'keranjang' ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400'">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03" />
            </svg>
            @if (count($cart) > 0)
                <span
                    class="absolute top-1 right-[38%] px-1.5 py-0.5 rounded-full text-[8px] font-bold bg-emerald-600 text-white leading-none shadow-sm">{{ count($cart) }}</span>
            @endif
            <span class="text-[10px] font-semibold mt-1">Keranjang</span>
        </button>
    </nav>

    <!-- Main Content Area -->
    <main x-ref="mainContent" @scroll.self="showScrollTop = $el.scrollTop > 300" class="flex-1 min-w-0 bg-[#f8fafc] dark:bg-gray-950 p-3 sm:p-6 lg:p-8 pb-28 md:p-6 md:pb-8 lg:p-8 overflow-y-auto"
          :class="{ 'text-size-sm': textSize === 'small', 'text-size-lg': textSize === 'large' }">

        <!-- Tab: Product Catalog -->
        <div x-show="activeTab === 'produk'" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6">

            <!-- Heading Row & Unified Search / Mode Scan Controls -->
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 md:gap-6">
                <!-- Heading (Left Side) -->
                <div class="min-w-0">
                    <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight md:leading-none">Katalog Produk</h1>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-1.5 md:mt-2 font-medium leading-5">Manajemen stok dan harga kebutuhan pokok harian</p>
                </div>

                <!-- Unified Controls Group (Right Side) - Zero Spacing Disconnect -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full lg:w-auto shrink-0">
                    <!-- Premium Search Bar & Telusuri Button Group -->
                    <div class="flex min-w-0 items-center bg-white dark:bg-gray-900 border border-gray-200/85 dark:border-gray-800 rounded-2xl shadow-sm overflow-hidden focus-within:ring-2 focus-within:ring-emerald-500/80 dark:focus-within:ring-emerald-500/50 transition-all duration-200 flex-1 sm:flex-initial">
                        <div class="relative flex items-center pl-4 py-2.5 bg-transparent flex-1 sm:flex-none">
                            <span class="pointer-events-none text-gray-400 dark:text-gray-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                            </span>
                            <input wire:model.live.debounce.300ms="search" type="text"
                                placeholder="Cari nama produk atau SKU..."
                                class="w-full sm:w-48 lg:w-60 pl-3 pr-4 bg-transparent text-xs text-gray-900 dark:text-white placeholder-gray-450 focus:outline-none border-none ring-0 focus:ring-0 focus:border-none" />
                        </div>
                        <button type="button" wire:click="$refresh"
                            class="shrink-0 px-3 md:px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white text-xs font-bold tracking-wide transition duration-150 cursor-pointer shadow-sm active:scale-95">
                            Telusuri
                        </button>
                    </div>

                    <!-- Mode Scan Button: Placed directly next to the search group with matching height and zero space gap -->
                    <button type="button" @click="setTab(activeTab === 'scan' ? 'produk' : 'scan')"
                        class="shrink-0 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-2xl shadow-sm text-xs font-bold transition-all active:scale-[0.98] cursor-pointer"
                        :class="activeTab === 'scan'
                            ? 'bg-amber-600 hover:bg-amber-700 text-white'
                            : 'bg-[#006b52] hover:bg-[#00523e] text-white'">
                        <!-- Scan Grid Icon -->
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        </svg>
                        <span x-text="activeTab === 'scan' ? 'Mode Katalog' : 'Mode Scan'">Mode Scan</span>
                    </button>
                </div>
            </div>

            @if ($lowStockProducts->isNotEmpty())
                <div
                    class="rounded-3xl border border-rose-200 bg-rose-50 p-5 shadow-sm dark:border-rose-900/50 dark:bg-rose-950/20">
                    <div class="flex flex-col gap-5 xl:flex-row xl:items-start">
                        <div class="min-w-0 xl:w-72 xl:shrink-0">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-2xl bg-rose-100 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300">
                                    <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5"
                                        viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-black text-rose-800 dark:text-rose-200">Stok Menipis</p>
                                    <p class="text-3xl font-black text-rose-950 dark:text-rose-100">
                                        {{ number_format($lowStockProductsCount, 0, ',', '.') }}
                                    </p>
                                </div>
                            </div>
                            <p class="mt-3 text-sm font-semibold leading-6 text-rose-800 dark:text-rose-200">
                                Produk ini sudah di bawah batas stok rendah.
                            </p>
                        </div>

                        <div class="grid min-w-0 flex-1 gap-3 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($lowStockProducts as $lowStockProduct)
                                <div
                                    class="rounded-2xl border border-rose-200 bg-white p-4 shadow-sm dark:border-rose-900/50 dark:bg-gray-900">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <p class="text-sm font-black leading-5 text-gray-950 break-words dark:text-white">
                                                {{ $lowStockProduct->name }}
                                            </p>
                                            <p class="mt-1 text-xs font-semibold leading-5 text-gray-600 break-words dark:text-gray-300">
                                                {{ $lowStockProduct->category?->name ?? 'Tanpa kategori' }}
                                            </p>
                                        </div>
                                        <span
                                            class="shrink-0 rounded-lg bg-rose-100 px-3 py-1.5 text-xs font-black text-rose-800 ring-1 ring-rose-200 dark:bg-rose-500/15 dark:text-rose-200 dark:ring-rose-500/30">
                                            Stok: {{ number_format($lowStockProduct->stock, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    <p class="mt-3 text-xs font-black text-rose-700 dark:text-rose-300">
                                        Batas rendah:
                                        {{ number_format($lowStockProduct->low_stock_threshold, 0, ',', '.') }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if ($lowStockProductsCount > $lowStockProducts->count())
                        <p class="mt-4 text-xs font-black text-rose-800 dark:text-rose-200">
                            +{{ number_format($lowStockProductsCount - $lowStockProducts->count(), 0, ',', '.') }}
                            produk lain juga stoknya menipis.
                        </p>
                    @endif
                </div>
            @endif

            <!-- Category Horizontally Scrollable Menu -->
            <div class="flex flex-wrap gap-2.5 overflow-x-auto pb-2 scrollbar-none">
                <button type="button" wire:click.prevent="selectCategory('all')"
                    class="px-5 py-2.5 rounded-full text-xs font-semibold border transition-all duration-200 cursor-pointer shadow-sm
                        {{ $category === 'all'
                            ? 'bg-[#006b52] text-white border-[#006b52]'
                            : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border-gray-150 dark:border-gray-800/80 hover:border-emerald-350 hover:text-emerald-700' }}">
                    Semua
                </button>
                @foreach ($categories as $cat)
                    <button type="button" wire:click.prevent="selectCategory('{{ $cat->id }}')"
                        class="px-5 py-2.5 rounded-full text-xs font-semibold border transition-all duration-200 cursor-pointer shadow-sm
                            {{ $category == $cat->id
                                ? 'bg-[#006b52] text-white border-[#006b52]'
                                : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400 border-gray-150 dark:border-gray-800/80 hover:border-emerald-350 hover:text-emerald-700' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            <!-- Product Responsive Grid (4 columns on desktop) -->
            <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-3 md:gap-6">
                @foreach ($products as $product)
                    <div wire:key="product-card-{{ $product->id }}"
                        @if ($product->stock > 0) wire:click.prevent="addToCart({{ $product->id }})" @endif
                        class="group relative bg-white dark:bg-gray-900 rounded-2xl md:rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm transition-all duration-300 ease-out overflow-hidden flex flex-col justify-between select-none
                         {{ $product->stock > 0
                             ? 'cursor-pointer hover:-translate-y-2 hover:shadow-xl hover:shadow-emerald-500/5 hover:border-emerald-250 dark:hover:border-emerald-800/80 active:scale-[0.98]'
                             : 'cursor-not-allowed opacity-75' }}">
                        <div>
                            <!-- Availability status badge -->
                            <div class="absolute top-2 left-2 z-10 md:top-4 md:left-4">
                                <span
                                    class="inline-flex items-center gap-1 rounded-full px-2 md:px-3 py-1 text-[8px] md:text-[10px] font-bold tracking-wide shadow-sm
                                    {{ $product->stock > 0
                                        ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/30 dark:text-emerald-350'
                                        : 'bg-rose-50 text-rose-700 dark:bg-rose-950/30 dark:text-rose-350' }}">
                                    <span
                                        class="h-1.5 w-1.5 rounded-full {{ $product->stock > 0 ? 'bg-emerald-500' : 'bg-rose-500' }}"></span>
                                    {{ $product->stock > 0 ? 'TERSEDIA' : 'HABIS' }}
                                </span>
                            </div>

                            <!-- Product Image -->
                            <div
                                class="aspect-[4/3] w-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center overflow-hidden border-b border-gray-50 dark:border-gray-850">
                                @if ($product->image)
                                    <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}"
                                        class="w-full h-full object-cover object-center transition duration-300 group-hover:scale-105" />
                                @else
                                    <div
                                        class="flex flex-col items-center justify-center w-full h-full text-gray-300 dark:text-gray-600 bg-gradient-to-br from-gray-50 to-gray-100 dark:from-gray-800 dark:to-gray-900">
                                        <svg class="w-10 h-10 mb-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <div class="p-3 md:p-5">
                                <!-- Category Name -->
                                <span
                                    class="text-[8px] md:text-[10px] uppercase tracking-widest font-bold text-emerald-600 dark:text-emerald-450 block mb-1">
                                    {{ $product->category?->name ?? 'UMUM' }}
                                </span>
                                <!-- Product Name -->
                                <h3
                                    class="text-xs md:text-sm font-semibold text-gray-800 dark:text-white mb-1.5 line-clamp-2 leading-snug group-hover:text-emerald-700 dark:group-hover:text-emerald-400 transition-colors">
                                    {{ $product->name }}
                                </h3>
                            </div>
                        </div>

                        <!-- Price & Stock Footer -->
                        <div class="px-3 md:px-5 pb-3 md:pb-5 pt-0">
                            <p class="text-sm md:text-base font-bold text-gray-900 dark:text-white mb-2 md:mb-3">
                                Rp {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                            <div
                                    class="flex items-center justify-between gap-2 md:gap-3 pt-2.5 border-t border-gray-50 dark:border-gray-800/80">
                                <span
                                    class="text-[10px] md:text-xs font-semibold {{ $product->stock > 0 ? 'text-gray-400 dark:text-gray-500' : 'text-rose-500' }}">
                                    Stok: {{ $product->stock }} unit
                                </span>

                                @if ($product->stock > 0)
                                    <div
                                        class="w-7 h-7 md:w-8 md:h-8 rounded-xl bg-gray-50 group-hover:bg-emerald-50 dark:bg-gray-800 dark:group-hover:bg-emerald-950/40 text-gray-650 group-hover:text-emerald-600 dark:text-gray-300 dark:group-hover:text-emerald-450 flex items-center justify-center transition-all duration-200 border border-gray-100 dark:border-gray-700/50 group-hover:border-emerald-100 dark:group-hover:border-emerald-900/30">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div
                                        class="w-7 h-7 md:w-8 md:h-8 rounded-xl bg-gray-50 dark:bg-gray-850 text-gray-350 dark:text-gray-700 flex items-center justify-center border border-gray-50 dark:border-gray-850">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination block -->
            @if ($products->hasPages())
                <div
                    class="mt-8 bg-white dark:bg-gray-900 p-4 border border-gray-100 dark:border-gray-800 rounded-3xl shadow-sm">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

        <!-- Tab: Scan QR -->
        <div x-show="activeTab === 'scan'" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-5 md:space-y-8 max-w-4xl mx-auto py-2 md:py-6" style="display: none;">

            <!-- Header Section -->
            <div class="text-center space-y-2 md:space-y-3">
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">Scan QR untuk Input
                    &amp; Pesanan</h1>
                <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 font-medium max-w-xl mx-auto leading-relaxed">
                    Arahkan kamera ke barcode/QR code produk untuk memasukkan item secara otomatis ke dalam keranjang
                    pesanan.
                </p>
            </div>

            <!-- Scanner Viewfinder Container -->
            <div
                class="relative bg-gray-950 rounded-3xl border border-gray-800/80 shadow-2xl overflow-hidden aspect-[3/4] md:aspect-[16/9] w-full flex flex-col justify-between p-4 md:p-6">

                <!-- Scanner Viewfinder Header with Mode Badge -->
                <div class="z-10 flex justify-center w-full">
                    <div
                        class="bg-black/60 backdrop-blur-md px-3 md:px-4 py-2 rounded-full border border-gray-800 flex items-center gap-2.5 md:gap-4 text-[10px] md:text-xs font-bold select-none">
                        <span class="text-gray-500 cursor-not-allowed">MODE INPUT</span>
                        <span
                            class="px-3 py-1 bg-emerald-600/90 text-white rounded-full shadow-sm tracking-wide animate-pulse">MODE
                            PESANAN</span>
                    </div>
                </div>

                <!-- Viewfinder Center Target Overlay with laser line animation -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none z-10">
                    <!-- Target Box with styled corner borders -->
                    <div
                        class="relative w-[82%] md:w-[65%] h-[34%] md:h-[40%] max-w-lg border border-emerald-500/20 rounded-2xl">
                        <!-- Corners styling -->
                        <div
                            class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-emerald-500 rounded-tl-xl -mt-[2px] -ml-[2px]">
                        </div>
                        <div
                            class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-emerald-500 rounded-tr-xl -mt-[2px] -mr-[2px]">
                        </div>
                        <div
                            class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-emerald-500 rounded-bl-xl -mb-[2px] -ml-[2px]">
                        </div>
                        <div
                            class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-emerald-500 rounded-br-xl -mb-[2px] -mr-[2px]">
                        </div>

                        <!-- Red/Green moving laser scanning animation -->
                        <div
                            class="w-full h-[3px] bg-gradient-to-r from-transparent via-emerald-400 to-transparent shadow-lg shadow-emerald-400/50 absolute left-0 animate-scanner-laser">
                        </div>
                    </div>
                </div>

                <!-- HTML5 QR Code Native Target Camera Feed -->
                <div id="sales-barcode-reader" class="absolute inset-0 w-full h-full object-cover rounded-3xl"></div>

                <!-- Bottom Controls Inside Viewfinder -->
                <div class="z-10 flex justify-center gap-2 md:gap-3 w-full">
                    <button type="button" @click="senterOn = !senterOn"
                        class="bg-black/60 hover:bg-black/80 backdrop-blur-md text-white px-3 md:px-4 py-2.5 rounded-full border border-gray-800/80 text-xs font-semibold flex items-center gap-2 transition-all active:scale-95 cursor-pointer">
                        <!-- Flashlight Icon -->
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                        </svg>
                        <span x-text="senterOn ? 'Senter: ON' : 'Senter'">Senter</span>
                    </button>
                    <button type="button" @click="toggleCamera()"
                        class="bg-black/60 hover:bg-black/80 backdrop-blur-md text-white px-3 md:px-4 py-2.5 rounded-full border border-gray-800/80 text-xs font-semibold flex items-center gap-2 transition-all active:scale-95 cursor-pointer">
                        <!-- Camera Switch Icon -->
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Putar</span>
                    </button>
                </div>
            </div>

            <!-- Status Indicator Banner -->
            <div class="text-center">
                <p class="text-sm font-semibold text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-900 inline-block px-5 py-2.5 rounded-2xl shadow-sm border border-gray-200/50 dark:border-gray-850"
                    x-text="scannerStatus">
                    Menyiapkan kamera...
                </p>
            </div>

            <!-- ATAU Divider -->
            <div class="relative flex py-2 items-center">
                <div class="flex-grow border-t border-gray-200 dark:border-gray-800"></div>
                <span class="flex-shrink mx-4 text-xs font-bold text-gray-400 tracking-wider">ATAU</span>
                <div class="flex-grow border-t border-gray-200 dark:border-gray-800"></div>
            </div>

            <!-- Upload from Gallery Control -->
            <div class="flex justify-center">
                <label
                    class="w-full justify-center md:w-auto bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-850 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-800 px-6 py-3.5 rounded-2xl shadow-sm text-sm font-semibold flex items-center gap-3 transition active:scale-[0.98] cursor-pointer">
                    <svg class="w-5 h-5 text-[#006b52] dark:text-emerald-450" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span>Unggah dari Galeri</span>
                    <input type="file" accept="image/*" class="hidden"
                        @change="
                                const file = $event.target.files[0];
                                if (file && window.SiKaMoBarcodeScanner) {
                                    scannerStatus = 'Membaca gambar dari galeri...';
                                    const html5Qr = new Html5Qrcode('sales-barcode-reader');
                                    html5Qr.scanFile(file, true)
                                        .then(async (decodedText) => {
                                            scannerStatus = `Barcode/QR terbaca: ${decodedText}`;
                                            await $wire.addByBarcode(decodedText);
                                        })
                                        .catch(err => {
                                            scannerStatus = 'Gambar tidak terbaca atau tidak mengandung barcode/QR.';
                                        });
                                }
                            " />
                </label>
            </div>

            <!-- Alert Banner Info -->
            <div
                class="bg-emerald-50/50 dark:bg-emerald-950/20 text-emerald-800 dark:text-emerald-300 border border-emerald-100/50 dark:border-emerald-900/30 px-5 py-4 rounded-2xl flex items-start gap-3.5 max-w-xl mx-auto shadow-sm">
                <svg class="w-5 h-5 mt-0.5 text-emerald-600 dark:text-emerald-450 flex-shrink-0" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="text-xs font-semibold leading-relaxed">
                    Pastikan kode QR terlihat jelas dan berada di dalam area kotak penargetan.
                </span>
            </div>
        </div>

        <!-- Tab: Cart Details -->
        <div x-show="activeTab === 'keranjang'" x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
            class="space-y-6" style="display: none;">

            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between mb-2">
                <div class="min-w-0">
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white tracking-tight">Detail Keranjang</h1>
                    <p class="text-xs md:text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium font-sans leading-5">Kelola daftar item
                        belanjaan dan selesaikan transaksi penjualan.</p>
                </div>
                <button type="button" @click="setTab('produk')"
                    class="inline-flex w-fit items-center gap-2 text-sm text-emerald-600 dark:text-emerald-400 font-semibold hover:text-emerald-700 transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>Kembali Belanja</span>
                </button>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 md:gap-8">
                <!-- Cart Items List -->
                <div class="lg:col-span-2 space-y-4">
                    @if (count($cart) > 0)
                        <div
                            class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-3 md:p-6 space-y-3 md:space-y-4">
                            @foreach ($cart as $item)
                                <div
                                    class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 md:gap-4 p-3 md:p-4 bg-gray-50 dark:bg-gray-800/40 rounded-2xl border border-gray-100 dark:border-gray-800/60 hover:border-emerald-100 dark:hover:border-emerald-900/30 transition-colors">
                                    <div class="flex-1 min-w-0">
                                        <h4
                                            class="font-semibold text-gray-800 dark:text-white text-sm md:text-base break-words sm:truncate leading-snug">
                                            {{ $item['name'] }}</h4>
                                        <p class="text-sm text-emerald-600 dark:text-emerald-450 font-bold mt-1">Rp
                                            {{ number_format($item['price'], 0, ',', '.') }}</p>
                                    </div>

                                    <div class="flex items-center justify-between sm:justify-end gap-3 md:gap-6">
                                        <!-- Quantity selector -->
                                        <div
                                            class="flex items-center gap-2 bg-white dark:bg-gray-900 p-1 border border-gray-100 dark:border-gray-800 rounded-xl shadow-sm">
                                            <button type="button"
                                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] - 1 }})"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-rose-50 hover:text-rose-600 dark:bg-gray-850 dark:hover:bg-rose-950/20 dark:hover:text-rose-405 rounded-lg text-xs font-bold transition-colors cursor-pointer border border-gray-100 dark:border-gray-800">−</button>
                                            <span
                                                class="text-sm font-semibold w-8 text-center text-gray-800 dark:text-gray-200">{{ $item['quantity'] }}</span>
                                            <button type="button"
                                                wire:click="updateQuantity({{ $item['id'] }}, {{ $item['quantity'] + 1 }})"
                                                class="w-8 h-8 flex items-center justify-center bg-gray-50 hover:bg-emerald-50 hover:text-emerald-600 dark:bg-gray-850 dark:hover:bg-emerald-950/20 dark:hover:text-emerald-405 rounded-lg text-xs font-bold transition-colors cursor-pointer border border-gray-100 dark:border-gray-800">+</button>
                                        </div>
                                        <!-- Subtotal detail -->
                                        <div class="text-right min-w-[100px] hidden sm:block">
                                            <span
                                                class="text-[10px] text-gray-400 block tracking-wider uppercase font-semibold">Subtotal</span>
                                            <span class="font-bold text-gray-800 dark:text-white text-sm">Rp
                                                {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}</span>
                                        </div>
                                        <!-- Remove item button -->
                                        <button type="button" wire:click="removeFromCart({{ $item['id'] }})"
                                            class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all cursor-pointer">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                </path>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div
                            class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-6 md:p-12 text-center">
                            <div
                                class="w-20 h-20 bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-450 rounded-full flex items-center justify-center mx-auto mb-6 shadow-sm">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-1.1 5H19M7 13l-1.1 5M7 13h10m0 0v8a2 2 0 01-2 2H9a2 2 0 01-2-2v-3.03">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-800 dark:text-white mb-2">Keranjang Anda Kosong</h3>
                            <p class="text-gray-400 dark:text-gray-500 max-w-sm mx-auto mb-6 text-sm font-medium">
                                Silakan pilih produk dari katalog belanja untuk ditambahkan ke keranjang belanja Anda.
                            </p>
                            <button type="button" @click="setTab('produk')"
                                class="px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-sm rounded-2xl shadow-md transition cursor-pointer">
                                Mulai Belanja
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Summary Checkout Card -->
                <div class="lg:col-span-1">
                    <div
                        class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-5 md:p-6 space-y-6 md:sticky md:top-8">
                        <h3
                            class="text-lg font-bold text-gray-900 dark:text-white border-b border-gray-50 dark:border-gray-800 pb-3">
                            Ringkasan Belanja</h3>

                        <div class="space-y-4 text-sm font-medium">
                            <div class="flex justify-between text-gray-500 dark:text-gray-400">
                                <span>Total Item</span>
                                <span class="font-semibold text-gray-800 dark:text-white">{{ count($cart) }}
                                    item</span>
                            </div>
                            <div
                                class="flex justify-between items-center pt-4 border-t border-gray-50 dark:border-gray-800">
                                <span class="text-base font-bold text-gray-850 dark:text-white">Total Bayar</span>
                                <span class="text-xl md:text-2xl font-black text-emerald-600 dark:text-emerald-450">Rp
                                    {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>

                        @if (count($cart) > 0)
                            <button type="button"
                                @click="cartTotal = $wire.total; selectedMethod = 'cash'; showPaymentModal = false; paymentStep = 1; setTab('pembayaran')"
                                class="w-full bg-emerald-700 hover:bg-emerald-800 text-white font-bold py-4 px-4 rounded-2xl transition-all shadow-md active:scale-[0.98] cursor-pointer text-center text-sm flex items-center justify-center gap-2">
                                <span>💳 Proses Pembayaran</span>
                            </button>
                        @else
                            <button type="button" disabled
                                class="w-full bg-gray-100 dark:bg-gray-800 text-gray-400 font-bold py-4 px-4 rounded-2xl cursor-not-allowed text-center text-sm flex items-center justify-center gap-2">
                                <span>💳 Keranjang Kosong</span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Page: Cash Checkout -->
        <div x-show="activeTab === 'pembayaran' && paymentStep === 1" x-cloak class="space-y-4 md:space-y-6"
            style="display: none;">
            <div
                class="w-full overflow-hidden rounded-2xl md:rounded-3xl bg-[#f8fafc] dark:bg-gray-950 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="grid lg:min-h-[calc(100vh-4rem)] lg:grid-cols-[minmax(0,1fr)_minmax(380px,0.62fr)]">
                    <section class="p-4 sm:p-8 lg:p-10 xl:p-12">
                        <div class="mb-4 md:mb-8">
                            <h3 class="text-2xl md:text-3xl font-black tracking-tight text-gray-900 dark:text-white">Pembayaran
                            </h3>
                            <p class="mt-1 text-xs font-semibold text-gray-400 dark:text-gray-500">Selesaikan transaksi
                                pelanggan</p>
                        </div>

                        <div
                            class="overflow-hidden rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-sm">
                            <div class="px-4 md:px-6 py-4 md:py-5 border-b border-gray-50 dark:border-gray-800">
                                <h4 class="text-base font-black text-gray-900 dark:text-white">Ringkasan Pesanan</h4>
                            </div>
                            <div class="md:hidden divide-y divide-gray-50 dark:divide-gray-800">
                                @foreach ($cart as $item)
                                    <div class="px-4 py-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div class="min-w-0 flex-1">
                                                <p class="break-words text-sm font-black text-gray-800 dark:text-white">
                                                    {{ $item['name'] }}
                                                </p>
                                                <p class="mt-1 text-[11px] font-bold text-gray-400 dark:text-gray-500">
                                                    {{ $item['quantity'] }} x Rp {{ number_format($item['price'], 0, ',', '.') }}
                                                </p>
                                            </div>
                                            <p class="shrink-0 text-right text-sm font-black text-gray-900 dark:text-white">
                                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="flex items-center justify-between gap-4 bg-gray-50/80 px-4 py-4 dark:bg-gray-900/80">
                                    <span class="text-sm font-black text-gray-600 dark:text-gray-300">Total Bayar</span>
                                    <span class="text-lg font-black text-emerald-600 dark:text-emerald-450">
                                        Rp {{ number_format($total, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                            <div class="hidden overflow-x-auto md:block">
                                <table class="w-full min-w-[520px] text-left">
                                    <thead
                                        class="bg-gray-50 dark:bg-gray-900/80 text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500">
                                        <tr>
                                            <th class="px-6 py-4">Produk</th>
                                            <th class="px-4 py-4 text-center">Jumlah</th>
                                            <th class="px-6 py-4 text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                        @foreach ($cart as $item)
                                            <tr>
                                                <td
                                                    class="px-6 py-5 text-sm font-semibold text-gray-700 dark:text-gray-300">
                                                    {{ $item['name'] }}</td>
                                                <td
                                                    class="px-4 py-5 text-center text-sm font-bold text-gray-500 dark:text-gray-300">
                                                    {{ $item['quantity'] }}</td>
                                                <td
                                                    class="px-6 py-5 text-right text-sm font-black text-gray-800 dark:text-white">
                                                    Rp
                                                    {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50/80 dark:bg-gray-900/80">
                                        <tr>
                                            <td colspan="2"
                                                class="px-6 py-5 text-base font-black text-gray-600 dark:text-gray-300">
                                                Total Bayar</td>
                                            <td
                                                class="px-6 py-5 text-right text-2xl font-black text-emerald-600 dark:text-emerald-450">
                                                Rp {{ number_format($total, 0, ',', '.') }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </section>

                    <aside
                        class="border-t lg:border-t-0 lg:border-l border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 p-4 pb-24 sm:p-8 lg:p-10">
                        <h4 class="text-sm md:text-base font-black text-gray-900 dark:text-white mb-4 md:mb-5">Metode Pembayaran</h4>
                        <div class="space-y-3 md:space-y-4 mb-5 md:mb-6">
                            <button type="button" @click="selectedMethod = 'cash'; paymentStep = 1"
                                class="w-full rounded-2xl border p-3 md:p-4 text-left transition cursor-pointer"
                                :class="selectedMethod === 'cash' ?
                                    'border-emerald-600 bg-emerald-50 dark:bg-emerald-950/20' :
                                    'border-gray-150 dark:border-gray-800 hover:border-emerald-300'">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-700 text-white">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block text-xs font-black text-gray-900 dark:text-white">Tunai</span>
                                        <span
                                            class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500">Bayar
                                            dengan uang fisik</span>
                                    </span>
                                    <span
                                        class="h-5 w-5 rounded-full border-2 border-emerald-600 bg-white dark:bg-gray-900 flex items-center justify-center">
                                        <span class="h-2 w-2 rounded-full bg-emerald-600"></span>
                                    </span>
                                </div>
                            </button>

                            <button type="button"
                                @if ($qrisQrCodeUrl) @click="selectedMethod = 'qris'; paymentStep = 2" @endif
                                @disabled(! $qrisQrCodeUrl)
                                class="w-full rounded-2xl border p-3 md:p-4 text-left transition {{ $qrisQrCodeUrl ? 'border-gray-150 dark:border-gray-800 hover:border-emerald-300 cursor-pointer' : 'border-gray-100 dark:border-gray-800 opacity-60 cursor-not-allowed' }}">
                                <div class="flex items-center gap-3">
                                    <span
                                        class="flex h-10 w-10 items-center justify-center rounded-xl bg-gray-50 dark:bg-gray-950 text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-800">
                                        <svg class="h-5 w-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </span>
                                    <span class="min-w-0 flex-1">
                                        <span
                                            class="block text-xs font-black text-gray-900 dark:text-white">QRIS</span>
                                        <span
                                            class="block text-[10px] font-semibold text-gray-400 dark:text-gray-500">
                                            {{ $qrisQrCodeUrl ? 'Nominal otomatis sesuai total belanja' : 'Payload QRIS belum dikonfigurasi' }}</span>
                                    </span>
                                    <span
                                        class="h-5 w-5 rounded-full border-2 border-gray-200 dark:border-gray-700"></span>
                                </div>
                            </button>
                        </div>

                        <label
                            class="block text-[10px] font-black uppercase tracking-wider text-gray-400 dark:text-gray-500 mb-3">Uang
                            Diterima</label>
                        <div class="relative mb-4">
                            <span
                                class="absolute left-4 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400">Rp</span>
                            <input type="number" x-model="amountPaid" @input="calcChange()" min="0"
                                placeholder="0"
                                class="w-full rounded-2xl border border-gray-150 bg-gray-50 py-4 pl-10 pr-4 text-sm font-black text-gray-900 focus:border-transparent focus:outline-none focus:ring-2 focus:ring-emerald-500 dark:border-gray-800 dark:bg-gray-950 dark:text-white">
                        </div>

                        <div class="grid grid-cols-2 min-[390px]:grid-cols-3 gap-2.5 md:gap-3 mb-5 md:mb-6">
                            <template x-for="nominal in quickNominals" :key="nominal">
                                <button type="button" @click="amountPaid = nominal; calcChange()"
                                    class="rounded-xl border border-gray-150 dark:border-gray-800 px-2 py-3 text-[10px] font-black text-gray-500 dark:text-gray-400 hover:border-emerald-500 hover:text-emerald-700 dark:hover:text-emerald-400 transition cursor-pointer"
                                    x-text="'Rp ' + formatCurrency(nominal)"></button>
                            </template>
                        </div>

                        <div
                            class="rounded-2xl bg-gray-50 dark:bg-gray-950 border border-gray-100 dark:border-gray-800 p-4 md:p-5 mb-4 md:mb-5 space-y-3 md:space-y-4">
                            <div
                                class="flex items-center justify-between text-xs font-bold text-gray-500 dark:text-gray-400">
                                <span>Total</span>
                                <span class="text-gray-900 dark:text-white"
                                    x-text="'Rp ' + formatCurrency(cartTotal)"></span>
                            </div>
                            <div
                                class="flex items-center justify-between text-xs font-bold text-gray-500 dark:text-gray-400">
                                <span>Kembalian</span>
                                <span class="text-base font-black text-emerald-700 dark:text-emerald-400"
                                    x-text="'Rp ' + formatCurrency(change)"></span>
                            </div>
                        </div>

                        <button type="button" @click="confirmCash()"
                            :disabled="!amountPaid || Number(amountPaid) < cartTotal"
                            class="w-full rounded-2xl bg-emerald-700 px-4 py-4 text-sm font-black text-white shadow-md shadow-emerald-700/15 hover:bg-emerald-800 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none disabled:cursor-not-allowed transition cursor-pointer">
                            Selesaikan Transaksi
                        </button>
                        <div class="mt-4">
                            <button type="button" @click="setTab('keranjang'); resetPayment()"
                                class="w-full rounded-xl border border-gray-150 dark:border-gray-800 px-3 py-3 text-[11px] font-bold text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-950 transition cursor-pointer">Kembali
                                ke Keranjang</button>
                        </div>
                    </aside>
                </div>
            </div>
        </div>

        <!-- Payment Page: QRIS -->
        <div x-show="activeTab === 'pembayaran' && paymentStep === 2 && selectedMethod === 'qris'" x-cloak
            class="space-y-6" style="display: none;">
            <div
                class="w-full min-h-[calc(100dvh-8rem)] md:min-h-[calc(100vh-4rem)] overflow-hidden rounded-2xl md:rounded-3xl bg-[#f8fafc] dark:bg-gray-950 border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="grid lg:grid-cols-[320px_1fr] min-h-[calc(100dvh-8rem)] md:min-h-[calc(100vh-4rem)]">
                    <aside
                        class="bg-white dark:bg-gray-900 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-gray-800 p-4 sm:p-8 flex flex-col">
                        <h4 class="text-base font-black text-gray-900 dark:text-white mb-5">Ringkasan Pesanan</h4>
                        <div class="max-h-56 space-y-3 overflow-y-auto pr-1 md:max-h-none md:space-y-4">
                            @foreach ($cart as $item)
                                <div
                                    class="rounded-2xl border border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-950 p-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-14 w-14 shrink-0 items-center justify-center rounded-xl bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 font-black">
                                            {{ strtoupper(substr($item['name'], 0, 1)) }}
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="truncate text-xs font-black text-gray-900 dark:text-white">
                                                {{ $item['name'] }}</p>
                                            <p class="mt-1 text-[10px] font-semibold text-gray-400 dark:text-gray-500">
                                                {{ $item['quantity'] }} unit</p>
                                            <p class="mt-1 text-xs font-black text-emerald-600 dark:text-emerald-450">
                                                Rp {{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-auto pt-5 border-t border-gray-100 dark:border-gray-800 space-y-3">
                            <div
                                class="flex items-center justify-between text-xs font-bold text-gray-500 dark:text-gray-400">
                                <span>Subtotal</span>
                                <span class="text-gray-900 dark:text-white">Rp
                                    {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-black text-gray-900 dark:text-white">Total Bayar</span>
                                <span class="text-lg font-black text-emerald-600 dark:text-emerald-450">Rp
                                    {{ number_format($total, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </aside>

                    <section class="flex flex-col items-center justify-center p-4 sm:p-12 text-center">
                        <h3 class="text-2xl md:text-3xl font-black tracking-tight text-gray-900 dark:text-white">Pembayaran QRIS
                        </h3>
                        <p class="mt-2 text-xs font-semibold text-gray-400 dark:text-gray-500">Silakan pindai kode QR
                            untuk menyelesaikan transaksi</p>

                        <div
                            class="mt-6 md:mt-9 rounded-3xl bg-white dark:bg-gray-900 p-4 sm:p-10 shadow-2xl shadow-gray-900/10 border border-gray-100 dark:border-gray-800">
                            <div class="rounded-2xl bg-emerald-50/60 dark:bg-emerald-950/20 p-3 sm:p-6">
                                @if ($qrisQrCodeUrl)
                                    <img src="{{ $qrisQrCodeUrl }}"
                                        alt="QR Code QRIS {{ $merchantName }}"
                                        class="h-[min(72vw,16rem)] w-[min(72vw,16rem)] sm:h-72 sm:w-72 rounded-xl bg-white p-3 shadow-sm object-contain">
                                @else
                                    <div
                                        class="flex h-[min(72vw,16rem)] w-[min(72vw,16rem)] sm:h-72 sm:w-72 items-center justify-center rounded-xl bg-white p-6 text-center text-xs font-bold text-gray-400 shadow-sm dark:bg-gray-950 dark:text-gray-500">
                                        Payload QRIS belum dikonfigurasi di Profil Toko
                                    </div>
                                @endif
                            </div>
                            <div class="mt-6 border-t border-gray-100 dark:border-gray-800 pt-5">
                                <p
                                    class="text-[10px] font-black uppercase tracking-[0.24em] text-gray-350 dark:text-gray-600">
                                    QRIS Dinamis</p>
                            </div>
                        </div>

                        <div
                            class="mt-6 inline-flex items-center gap-2 rounded-full border border-emerald-100 dark:border-emerald-900/40 bg-emerald-50 dark:bg-emerald-950/20 px-4 py-2 text-xs font-bold text-emerald-700 dark:text-emerald-400">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Menunggu Pembayaran...
                        </div>

                        <button type="button" @click="confirmQris()" @disabled(! $qrisQrCodeUrl)
                            class="mt-6 w-full max-w-md rounded-2xl px-4 py-4 text-sm font-black text-white shadow-md transition {{ $qrisQrCodeUrl ? 'bg-emerald-700 shadow-emerald-700/15 hover:bg-emerald-800 cursor-pointer' : 'bg-gray-300 shadow-gray-300/20 cursor-not-allowed dark:bg-gray-700' }}">
                            Selesaikan Transaksi
                        </button>
                        <button type="button" @click="selectedMethod = 'cash'; paymentStep = 1"
                            class="mt-3 text-xs font-bold text-gray-400 dark:text-gray-500 hover:text-emerald-700 dark:hover:text-emerald-400 transition cursor-pointer">
                            Kembali ke Pembayaran Tunai
                        </button>
                        <button type="button" @click="setTab('keranjang'); resetPayment()"
                            class="mt-2 text-xs font-bold text-gray-400 dark:text-gray-500 hover:text-rose-600 transition cursor-pointer">
                            Batalkan Transaksi
                        </button>
                    </section>
                </div>
            </div>
        </div>
        <!-- Receipt Page -->
        <div x-show="activeTab === 'struk'" x-cloak
            class="min-h-[calc(100dvh-8rem)] md:min-h-[calc(100vh-4rem)] flex flex-col items-center justify-center py-4 md:py-8" style="display: none;">
            <div class="text-center mb-7">
                <div
                    class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full"
                    :class="receiptRefunded
                        ? 'bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400'
                        : 'bg-emerald-50 text-emerald-600 dark:bg-emerald-950/30 dark:text-emerald-400'">
                    <svg x-show="!receiptRefunded" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7">
                        </path>
                    </svg>
                    <svg x-show="receiptRefunded" class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="display: none;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M9 14 4 9m0 0 5-5M4 9h11a5 5 0 0 1 0 10h-1" />
                    </svg>
                </div>
                <h3 class="text-xl md:text-2xl font-black text-gray-900 dark:text-white"
                    x-text="receiptRefunded ? 'Refund Berhasil' : 'Pembayaran Berhasil'"></h3>
                <p class="mt-2 text-xs md:text-sm font-medium text-gray-500 dark:text-gray-400"
                    x-text="receiptRefunded
                        ? 'Transaksi telah direfund dan stok produk sudah dikembalikan.'
                        : 'Transaksi Anda telah diproses dengan sukses.'"></p>
            </div>

            <div id="receipt-print-area"
                class="w-full max-w-lg overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="px-5 md:px-8 py-6 md:py-7 text-center">
                    <h4 class="text-lg font-black text-emerald-600 dark:text-emerald-400"
                        x-text="receiptMerchantName"></h4>
                    <p class="mt-1 text-[11px] font-semibold text-gray-400 dark:text-gray-500"
                        x-text="receiptMerchantAddress"></p>
                    <div class="mt-6 space-y-2 text-left text-[11px] md:text-xs font-semibold text-gray-500 dark:text-gray-400">
                        <div class="flex justify-between gap-4">
                            <span>ID Transaksi</span>
                            <span class="min-w-0 break-all text-right font-mono text-gray-800 dark:text-gray-100" x-text="invoiceNumber"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Status</span>
                            <span class="font-black uppercase"
                                :class="receiptRefunded ? 'text-rose-600 dark:text-rose-400' :
                                    'text-emerald-600 dark:text-emerald-400'"
                                x-text="receiptRefunded ? 'Refund' : 'Berhasil'"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Waktu</span>
                            <span class="text-gray-800 dark:text-gray-100" x-text="receiptCreatedAt"></span>
                        </div>
                        <div class="flex justify-between gap-4">
                            <span>Metode Bayar</span>
                            <span class="uppercase text-gray-800 dark:text-gray-100"
                                x-text="receiptPaymentMethod"></span>
                        </div>
                    </div>
                </div>

                <div class="border-y border-dashed border-gray-200 px-5 md:px-8 py-4 md:py-5 dark:border-gray-700">
                    <template x-for="item in receiptItems" :key="item.name + item.quantity">
                        <div class="flex justify-between gap-5 py-3">
                            <div class="min-w-0 text-left">
                                <p class="break-words text-sm font-black text-gray-900 dark:text-white"
                                    x-text="item.name"></p>
                                <p class="mt-1 text-[11px] font-semibold text-gray-400 dark:text-gray-500">
                                    <span x-text="item.quantity"></span> unit x Rp <span
                                        x-text="formatCurrency(item.price)"></span>
                                </p>
                            </div>
                            <p class="shrink-0 text-right text-sm font-black text-gray-900 dark:text-white">Rp <span
                                    x-text="formatCurrency(item.subtotal)"></span></p>
                        </div>
                    </template>
                </div>

                <div class="px-5 md:px-8 py-5 space-y-4">
                    <div class="flex justify-between text-sm font-semibold text-gray-400 dark:text-gray-500">
                        <span>Subtotal</span>
                        <span class="text-gray-900 dark:text-white">Rp <span
                                x-text="formatCurrency(totalAmount)"></span></span>
                    </div>
                    <div class="flex items-center justify-between border-t border-gray-100 pt-4 dark:border-gray-800">
                        <span class="text-base font-black text-gray-900 dark:text-white"
                            x-text="receiptRefunded ? 'TOTAL REFUND' : 'TOTAL BAYAR'"></span>
                        <span
                            class="text-lg md:text-xl font-black"
                            :class="receiptRefunded ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400'">
                            Rp <span
                                x-text="formatCurrency(totalAmount)"></span></span>
                    </div>
                </div>

                <div class="bg-gray-50 px-5 md:px-8 py-6 text-center dark:bg-gray-950/50">
                    <p class="text-[11px] italic font-medium text-gray-400 dark:text-gray-500"
                        x-text="receiptRefunded ? 'Refund telah diproses oleh kasir.' : 'Terima kasih telah berbelanja.'"></p>
                    <p
                        class="receipt-powered mt-4 text-xs font-black tracking-[0.18em] text-emerald-600 dark:text-emerald-400">
                        POWERED BY SIKAMO</p>
                </div>
            </div>

            <div class="mt-5 md:mt-7 grid w-full max-w-lg grid-cols-1 gap-3" :class="receiptRefunded ? 'sm:grid-cols-2' : 'sm:grid-cols-3'">
                <button type="button" @click="printReceipt()" x-show="!receiptRefunded"
                    class="rounded-2xl bg-emerald-700 px-5 py-4 text-sm font-black text-white shadow-md shadow-emerald-700/15 hover:bg-emerald-800 transition cursor-pointer">
                    Cetak Struk
                </button>
                <button type="button" @click="refundReceipt()" x-bind:disabled="receiptRefunded || !invoiceNumber"
                    class="rounded-2xl bg-rose-600 px-5 py-4 text-sm font-black text-white shadow-md shadow-rose-600/15 hover:bg-rose-700 disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none disabled:cursor-not-allowed transition cursor-pointer">
                    <span x-text="receiptRefunded ? 'Sudah Refund' : 'Refund'"></span>
                </button>
                <button type="button" @click="closeReceipt(); setTab('produk')"
                    class="rounded-2xl border border-gray-150 bg-white px-5 py-4 text-sm font-black text-gray-600 hover:bg-gray-50 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-950 transition cursor-pointer">
                    Kembali ke Transaksi
                </button>
            </div>

            <div x-show="receiptReturnCountdown > 0" x-cloak
                class="mt-4 w-full max-w-lg rounded-2xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-center text-xs font-black text-emerald-700 shadow-sm dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-400"
                style="display: none;">
                Kembali otomatis ke Produk dalam <span x-text="receiptReturnCountdown"></span> detik
            </div>

            <div x-show="showRefundConfirm" x-cloak x-transition.opacity
                class="fixed inset-0 z-50 flex items-center justify-center bg-gray-950/70 px-4"
                x-on:keydown.escape.window="cancelRefundConfirm()" style="display: none;">
                <div class="absolute inset-0" x-on:click="cancelRefundConfirm()"></div>

                <div x-show="showRefundConfirm" x-transition
                    class="relative max-h-[calc(100dvh-2rem)] w-full max-w-md overflow-y-auto rounded-2xl border border-rose-100 bg-white shadow-2xl dark:border-rose-900/40 dark:bg-gray-900">
                    <div class="px-5 md:px-6 pt-6 text-center">
                        <div
                            class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-rose-50 text-rose-600 dark:bg-rose-950/30 dark:text-rose-400">
                            <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M9 14 4 9m0 0 5-5M4 9h11a5 5 0 0 1 0 10h-1" />
                            </svg>
                        </div>
                        <h3 class="mt-4 text-xl font-black text-gray-950 dark:text-white">Refund transaksi?</h3>
                        <p class="mt-2 text-sm font-medium leading-6 text-gray-500 dark:text-gray-400">
                            Transaksi <span class="font-mono font-bold text-gray-800 dark:text-gray-100"
                                x-text="invoiceNumber"></span> akan ditandai refund dan stok produk dikembalikan.
                        </p>
                    </div>

                    <div class="mt-6 grid grid-cols-1 gap-3 border-t border-gray-100 bg-gray-50 p-4 dark:border-gray-800 dark:bg-gray-950/50 sm:grid-cols-2">
                        <button type="button" x-on:click="cancelRefundConfirm()" x-bind:disabled="refundProcessing"
                            class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-black text-gray-600 transition hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-gray-800 dark:bg-gray-900 dark:text-gray-300 dark:hover:bg-gray-800">
                            Batal
                        </button>
                        <button type="button" x-on:click="confirmRefundReceipt()" x-bind:disabled="refundProcessing"
                            class="rounded-xl bg-rose-600 px-4 py-3 text-sm font-black text-white shadow-md shadow-rose-600/15 transition hover:bg-rose-700 disabled:cursor-not-allowed disabled:bg-rose-300">
                            <span x-show="!refundProcessing">Ya, Refund</span>
                            <span x-show="refundProcessing" style="display: none;">Memproses...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>



    <!-- Scroll to Top FAB -->
    <button
        x-show="showScrollTop"
        @click="$refs.mainContent.scrollTo({ top: 0, behavior: 'smooth' })"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-75 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-75 translate-y-4"
        class="fixed z-50 bottom-20 md:bottom-6 left-4 md:left-6
               w-12 h-12 rounded-2xl
               bg-gradient-to-br from-gray-700 to-gray-900 dark:from-gray-600 dark:to-gray-800
               shadow-lg shadow-gray-900/30 dark:shadow-black/50
               flex items-center justify-center
               hover:scale-110 hover:shadow-xl
               active:scale-95 transition-all duration-300 cursor-pointer
               group">
        <svg class="w-5 h-5 text-white group-hover:-translate-y-0.5 transition-transform duration-300" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
        </svg>
    </button>
</div>

@php
    $scannerScriptUrl = Illuminate\Support\Facades\Vite::asset('resources/js/barcode-scanner.js');
@endphp

@push('scripts')
    <style>
        html.text-size-sm {
            font-size: 14px !important;
        }
        html.text-size-lg {
            font-size: 18px !important;
        }

        @keyframes laserScan {

            0%,
            100% {
                top: 0%;
            }

            50% {
                top: 100%;
            }
        }

        .animate-scanner-laser {
            animation: laserScan 2.5s infinite ease-in-out;
        }

        @media print {

            html,
            body {
                width: 80mm !important;
                min-width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
            }

            body * {
                visibility: hidden !important;
            }

            #receipt-print-area,
            #receipt-print-area * {
                visibility: visible !important;
            }

            #receipt-print-area {
                position: absolute !important;
                inset: 0 auto auto 0 !important;
                transform: none !important;
                width: 72mm !important;
                max-width: 72mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff !important;
                color: #000000 !important;
                border: 0 !important;
                border-radius: 0 !important;
                box-shadow: none !important;
                font-family: "Courier New", Courier, monospace !important;
                font-size: 10px !important;
                line-height: 1.35 !important;
            }

            #receipt-print-area * {
                background: transparent !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                color: #000000 !important;
                font-family: inherit !important;
                letter-spacing: 0 !important;
            }

            #receipt-print-area>div {
                padding: 8px 0 !important;
            }

            #receipt-print-area>div:first-child {
                padding-top: 0 !important;
            }

            #receipt-print-area h4 {
                font-size: 14px !important;
                line-height: 1.2 !important;
                text-transform: uppercase !important;
            }

            #receipt-print-area p,
            #receipt-print-area span {
                font-size: 10px !important;
            }

            #receipt-print-area .text-xl,
            #receipt-print-area .text-lg,
            #receipt-print-area .text-base,
            #receipt-print-area .text-sm {
                font-size: 10px !important;
            }

            #receipt-print-area .border-y,
            #receipt-print-area .border-t {
                border-color: #000000 !important;
                border-style: dashed !important;
                border-width: 1px 0 0 0 !important;
            }

            #receipt-print-area .border-y {
                border-bottom-width: 1px !important;
            }

            #receipt-print-area .py-3 {
                padding-top: 5px !important;
                padding-bottom: 5px !important;
            }

            #receipt-print-area .gap-5,
            #receipt-print-area .gap-4 {
                gap: 8px !important;
            }

            #receipt-print-area .text-emerald-600,
            #receipt-print-area .text-emerald-400 {
                color: #000000 !important;
            }

            #receipt-print-area .receipt-powered {
                color: #000000 !important;
                font-size: 9px !important;
                letter-spacing: 1px !important;
            }
        }
    </style>
    <script>
        // Register Livewire → window bridge ONCE (guard prevents duplicate on re-render)
        if (!window.__sikamoListenersRegistered) {
            window.__sikamoListenersRegistered = true;

            document.addEventListener('livewire:init', function() {
                Livewire.on('showToast', function(data) {
                    const d = Array.isArray(data) ? data[0] : data;
                    const msg = d?.message || 'Notifikasi';
                    const type = d?.type || 'info';
                    if (window.toastr && toastr[type]) toastr[type](msg);
                });

                Livewire.on('checkoutSuccess', function(data) {
                    const d = Array.isArray(data) ? data[0] : data;
                    window.dispatchEvent(new CustomEvent('sikamo:checkoutSuccess', {
                        detail: d
                    }));
                });

                Livewire.on('checkoutError', function(data) {
                    const d = Array.isArray(data) ? data[0] : data;
                    const msg = d?.message || 'Transaksi gagal. Silakan coba lagi.';
                    if (window.toastr) toastr.error(msg);
                    window.dispatchEvent(new CustomEvent('sikamo:checkoutError', {
                        detail: d
                    }));
                });
            });
        }

        function checkoutData() {
            return {
                // Entangled properties
                activeTab: @entangle('activeTab'),

                // UI Preferences
                textSize: localStorage.getItem('sikamo_textSize') || 'medium',
                showScrollTop: false,

                setTextSize(size) {
                    this.textSize = size;
                    localStorage.setItem('sikamo_textSize', size);
                },

                // Checkout state
                showPaymentModal: false,
                paymentStep: 1,
                selectedMethod: '',
                amountPaid: '',
                change: 0,
                cartTotal: @entangle('total'),
                quickNominals: [5000, 10000, 20000, 50000, 100000, 200000],
                showCheckoutModal: false,
                invoiceNumber: '',
                totalAmount: 0,
                receiptAmountPaid: 0,
                receiptChange: 0,
                receiptPaymentMethod: '',
                receiptCreatedAt: '',
                receiptMerchantName: '',
                receiptMerchantAddress: '',
                receiptCashierName: '',
                receiptItems: [],
                receiptRefunded: false,
                receiptReturnCountdown: 0,
                receiptReturnTimer: null,
                showRefundConfirm: false,
                refundProcessing: false,
                realtimeChannel: @js($merchantRealtimeChannel),
                realtimeRefreshTimer: null,

                // Scanning states
                scanner: null,
                scanning: false,
                scannerReady: false,
                scannerLoadStarted: false,
                pendingStart: false,
                scannerStatus: 'Menyiapkan kamera...',
                senterOn: false,
                facingMode: 'environment', // Kamera belakang default

                init() {
                    this.initRealtime();

                    // Listen to native window events (bridged from Livewire once, never duplicated)
                    window.addEventListener('sikamo:checkoutSuccess', (e) => {
                        const d = e.detail;
                        this.invoiceNumber = d.invoiceNumber || '';
                        this.totalAmount = d.total || 0;
                        this.receiptAmountPaid = d.amountPaid || 0;
                        this.receiptChange = d.change || 0;
                        this.receiptPaymentMethod = d.paymentMethod || '';
                        this.receiptCreatedAt = d.createdAt || '';
                        this.receiptMerchantName = d.merchantName || 'Toko Kelontong';
                        this.receiptMerchantAddress = d.merchantAddress || 'SiKaMo POS';
                        this.receiptCashierName = d.cashierName || '';
                        this.receiptItems = d.items || [];
                        this.receiptRefunded = false;
                        this.stopReceiptReturnCountdown();
                        this.showPaymentModal = false;
                        this.resetPayment();
                        this.setTab('struk');
                    });

                    window.addEventListener('sikamo:checkoutError', (e) => {
                        this.showPaymentModal = false;
                        this.resetPayment();
                    });

                    // Watch activeTab to start/stop scanner automatically
                    this.$watch('activeTab', (value) => {
                        if (value === 'scan') {
                            this.startScanning();
                        } else {
                            this.stopScanning();
                        }
                    });

                    // Watch senterOn to apply torch dynamically
                    this.$watch('senterOn', async (on) => {
                        if (this.scanner && typeof this.scanner.setTorch === 'function') {
                            await this.scanner.setTorch(on);
                        }
                    });

                    if (this.activeTab === 'scan') {
                        this.startScanning();
                    }

                    // Watch textSize to apply styles to document element
                    this.$watch('textSize', (size) => {
                        document.documentElement.classList.remove('text-size-sm', 'text-size-lg');
                        if (size === 'small') document.documentElement.classList.add('text-size-sm');
                        if (size === 'large') document.documentElement.classList.add('text-size-lg');
                    });

                    // Initialize text size on load
                    const initSize = this.textSize;
                    document.documentElement.classList.remove('text-size-sm', 'text-size-lg');
                    if (initSize === 'small') document.documentElement.classList.add('text-size-sm');
                    if (initSize === 'large') document.documentElement.classList.add('text-size-lg');
                },

                setTab(tab) {
                    if (tab !== 'struk') {
                        this.stopReceiptReturnCountdown();
                    }

                    this.activeTab = tab;
                    this.$wire.setTab(tab);
                },

                initRealtime() {
                    if (!this.realtimeChannel || !window.Echo) {
                        return;
                    }

                    window.Echo.private(this.realtimeChannel)
                        .listen('.ProductStockUpdated', (event) => {
                            this.scheduleRealtimeRefresh();

                            if (event?.productName) {
                                window.toastr?.info(`Stok ${event.productName} diperbarui: ${event.stock}`);
                            }
                        });
                },

                scheduleRealtimeRefresh() {
                    clearTimeout(this.realtimeRefreshTimer);

                    this.realtimeRefreshTimer = setTimeout(() => {
                        if (typeof this.$wire?.$refresh === 'function') {
                            this.$wire.$refresh();
                            return;
                        }

                        this.$wire.call('$refresh');
                    }, 250);
                },

                async startScanning() {
                    this.waitUntilScannerReady();
                },

                waitUntilScannerReady() {
                    if (window.SiKaMoBarcodeScanner) {
                        this.scannerReady = true;
                        this.scannerStatus = 'Scanner siap. Membuka kamera...';
                        this.triggerScanner();
                        return;
                    }

                    if (!this.scannerLoadStarted) {
                        this.scannerLoadStarted = true;
                        this.loadScannerScript();
                    }

                    setTimeout(() => this.waitUntilScannerReady(), 100);
                },

                loadScannerScript() {
                    const scriptUrl = '{{ $scannerScriptUrl }}';

                    if (document.querySelector('script[data-sikamo-barcode-scanner=true]')) {
                        return;
                    }

                    const script = document.createElement('script');
                    script.type = 'module';
                    script.src = scriptUrl;
                    script.dataset.sikamoBarcodeScanner = 'true';
                    script.onerror = () => {
                        this.scannerStatus = 'Scanner gagal dimuat. Refresh halaman lalu coba lagi.';
                    };
                    document.head.appendChild(script);
                },

                async triggerScanner() {
                    try {
                        if (this.scanning) return;
                        this.scanning = true;
                        this.scannerStatus = 'Membuka kamera...';

                        this.scanner = await window.SiKaMoBarcodeScanner.start({
                            elementId: 'sales-barcode-reader',
                            facingMode: this.facingMode,
                            onStatus: (message) => this.scannerStatus = message,
                            onDetected: async (value) => {
                                this.scannerStatus = `Barcode/QR terbaca: ${value}`;

                                await this.$wire.addByBarcode(value);

                                // Resume scanning automatically after 2s delay
                                setTimeout(async () => {
                                    if (this.activeTab === 'scan') {
                                        this.scanning = false;
                                        await this.triggerScanner();
                                    }
                                }, 2000);
                            },
                        });

                        // Set torch state if previously toggled on
                        if (this.senterOn && typeof this.scanner.setTorch === 'function') {
                            await this.scanner.setTorch(true);
                        }
                    } catch (error) {
                        this.scanning = false;
                        this.scannerStatus = error?.message ||
                            'Kamera tidak bisa diakses. Izinkan kamera atau pastikan situs aman (HTTPS/localhost).';
                    }
                },

                stopScanning() {
                    this.scanning = false;
                    if (this.scanner) {
                        this.scanner.stop();
                        this.scanner = null;
                    }
                },

                async toggleCamera() {
                    this.facingMode = this.facingMode === 'environment' ? 'user' : 'environment';
                    this.scannerStatus =
                        `Mengganti kamera (${this.facingMode === 'environment' ? 'Kamera Belakang' : 'Kamera Depan'})...`;
                    await this.stopScanning();

                    setTimeout(async () => {
                        if (this.activeTab === 'scan') {
                            await this.triggerScanner();
                        }
                    }, 500);
                },

                calcChange() {
                    const paid = parseFloat(this.amountPaid) || 0;
                    this.change = paid >= this.cartTotal ? paid - this.cartTotal : 0;
                },

                confirmCash() {
                    const paid = parseFloat(this.amountPaid);
                    if (!paid || paid < this.cartTotal) {
                        return;
                    }
                    this.$wire.call('checkout', 'cash', paid);
                },

                confirmQris() {
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

                closeReceipt() {
                    this.stopReceiptReturnCountdown();
                    this.showCheckoutModal = false;
                    this.invoiceNumber = '';
                    this.totalAmount = 0;
                    this.receiptAmountPaid = 0;
                    this.receiptChange = 0;
                    this.receiptPaymentMethod = '';
                    this.receiptCreatedAt = '';
                    this.receiptMerchantName = '';
                    this.receiptMerchantAddress = '';
                    this.receiptCashierName = '';
                    this.receiptItems = [];
                    this.receiptRefunded = false;
                    this.showRefundConfirm = false;
                    this.refundProcessing = false;
                },

                refundReceipt() {
                    if (!this.invoiceNumber || this.receiptRefunded) {
                        return;
                    }

                    this.stopReceiptReturnCountdown();
                    this.showRefundConfirm = true;
                },

                startReceiptReturnCountdown() {
                    this.stopReceiptReturnCountdown();
                    this.receiptReturnCountdown = 10;

                    this.receiptReturnTimer = setInterval(() => {
                        this.receiptReturnCountdown -= 1;

                        if (this.receiptReturnCountdown <= 0) {
                            this.stopReceiptReturnCountdown();
                            this.closeReceipt();
                            this.setTab('produk');
                        }
                    }, 1000);
                },

                stopReceiptReturnCountdown() {
                    if (this.receiptReturnTimer) {
                        clearInterval(this.receiptReturnTimer);
                        this.receiptReturnTimer = null;
                    }

                    this.receiptReturnCountdown = 0;
                },

                cancelRefundConfirm() {
                    if (this.refundProcessing) {
                        return;
                    }

                    this.showRefundConfirm = false;
                },

                async confirmRefundReceipt() {
                    if (!this.invoiceNumber || this.receiptRefunded || this.refundProcessing) {
                        return;
                    }

                    this.refundProcessing = true;
                    const refunded = await this.$wire.refundTransaction(this.invoiceNumber);

                    if (refunded) {
                        this.receiptRefunded = true;
                        this.showRefundConfirm = false;
                    }

                    this.refundProcessing = false;
                },

                printReceipt() {
                    if (this.receiptRefunded) {
                        return;
                    }

                    const escapeHtml = (value) => String(value ?? '')
                        .replace(/&/g, '&amp;')
                        .replace(/</g, '&lt;')
                        .replace(/>/g, '&gt;')
                        .replace(/"/g, '&quot;')
                        .replace(/'/g, '&#039;');

                    const items = this.receiptItems || [];
                    const rows = items.map((item) => `
                    <div class="item">
                        <div class="line strong">
                            <span>${escapeHtml(item.name)}</span>
                            <span>Rp ${this.formatCurrency(item.subtotal)}</span>
                        </div>
                        <div class="muted">${item.quantity} x Rp ${this.formatCurrency(item.price)}</div>
                    </div>
                `).join('');

                    const isCashReceipt = this.receiptPaymentMethod === 'cash';
                    const originalTitle = document.title;
                    const printTitle = this.invoiceNumber || 'Struk SiKaMo';
                    document.title = printTitle;

                    const oldFrame = document.getElementById('sikamo-print-frame');
                    if (oldFrame) oldFrame.remove();

                    const printFrame = document.createElement('iframe');
                    printFrame.id = 'sikamo-print-frame';
                    printFrame.style.position = 'fixed';
                    printFrame.style.right = '0';
                    printFrame.style.bottom = '0';
                    printFrame.style.width = '0';
                    printFrame.style.height = '0';
                    printFrame.style.border = '0';
                    printFrame.style.opacity = '0';
                    document.body.appendChild(printFrame);

                    const printDocument = printFrame.contentDocument || printFrame.contentWindow.document;
                    printDocument.open();
                    printDocument.write(`
                    <!doctype html>
                    <html>
                        <head>
                            <meta charset="utf-8">
                            <title>${escapeHtml(printTitle)}</title>
                            <style>
                                @page { size: 80mm 200mm; margin: 0; }
                                html, body {
                                    width: 80mm;
                                    margin: 0;
                                    padding: 0;
                                    background: #fff;
                                    color: #000;
                                    font-family: "Courier New", Courier, monospace;
                                    font-size: 10.5px;
                                    line-height: 1.3;
                                }
                                * { box-sizing: border-box; color: #000; font-family: inherit; }
                                .receipt { width: 74mm; margin: 3mm 3mm 0 3mm; padding: 0; }
                                .center { text-align: center; }
                                .store {
                                    font-size: 13px;
                                    font-weight: 800;
                                    text-transform: uppercase;
                                    margin-bottom: 3px;
                                }
                                .address {
                                    font-size: 9.5px;
                                    margin-bottom: 10px;
                                }
                                .line {
                                    display: flex;
                                    justify-content: space-between;
                                    gap: 8px;
                                }
                                .line span:first-child {
                                    min-width: 0;
                                    overflow-wrap: anywhere;
                                }
                                .strong { font-weight: 800; }
                                .muted { font-size: 9.5px; }
                                .rule {
                                    border-top: 1px dashed #000;
                                    margin: 8px 0;
                                }
                                .meta .line { margin: 3px 0; }
                                .item { margin: 7px 0; }
                                .total {
                                    font-size: 12px;
                                    font-weight: 900;
                                    margin-top: 8px;
                                }
                                .payment-lines,
                                .footer {
                                    break-inside: avoid;
                                    page-break-inside: avoid;
                                    page-break-before: avoid;
                                }
                                .thanks {
                                    margin-top: 14px;
                                    text-align: center;
                                    font-size: 9.5px;
                                    font-style: italic;
                                }
                                .powered {
                                    margin-top: 9px;
                                    margin-bottom: 0;
                                    text-align: center;
                                    font-size: 9px;
                                    font-weight: 900;
                                    letter-spacing: 1px;
                                }
                            </style>
                        </head>
                        <body>
                            <div id="thermal-receipt" class="receipt">
                                <div class="center">
                                    <div class="store">${escapeHtml(this.receiptMerchantName || 'SIKAMO')}</div>
                                    <div class="address">${escapeHtml(this.receiptMerchantAddress || 'SiKaMo POS')}</div>
                                </div>

                                <div class="meta">
                                    <div class="line"><span>ID</span><span>${escapeHtml(this.invoiceNumber)}</span></div>
                                    <div class="line"><span>Waktu</span><span>${escapeHtml(this.receiptCreatedAt)}</span></div>
                                    <div class="line"><span>Kasir</span><span>${escapeHtml(this.receiptCashierName || '-')}</span></div>
                                    <div class="line"><span>Metode</span><span>${escapeHtml(this.receiptPaymentMethod).toUpperCase()}</span></div>
                                </div>

                                <div class="rule"></div>
                                ${rows}
                                <div class="rule"></div>

                                <div class="line"><span>Subtotal</span><span>Rp ${this.formatCurrency(this.totalAmount)}</span></div>
                                <div class="rule"></div>
                                <div class="line total"><span>TOTAL</span><span>Rp ${this.formatCurrency(this.totalAmount)}</span></div>
                                <div class="rule"></div>
                                ${isCashReceipt ? `
                                        <div class="payment-lines">
                                            <div class="line"><span>Cash</span><span>Rp ${this.formatCurrency(this.receiptAmountPaid)}</span></div>
                                            <div class="line"><span>Kembalian</span><span>Rp ${this.formatCurrency(this.receiptChange)}</span></div>
                                        </div>
                                    ` : ''}

                                <div class="footer">
                                    <div class="thanks">Terima kasih telah berbelanja.</div>
                                    <div class="powered">POWERED BY SIKAMO</div>
                                </div>
                            </div>
                        </body>
                    </html>
                `);
                    printDocument.close();

                    setTimeout(() => {
                        const receiptElement = printDocument.getElementById('thermal-receipt');
                        const receiptHeightPx = receiptElement ? receiptElement.getBoundingClientRect().height :
                        360;
                        const receiptHeightMm = Math.ceil((receiptHeightPx * 25.4) / 96) + 5;
                        const exactPageStyle = printDocument.createElement('style');
                        exactPageStyle.textContent = `@page { size: 80mm ${receiptHeightMm}mm; margin: 0; }`;
                        printDocument.head.appendChild(exactPageStyle);

                        const cleanup = () => {
                            document.title = originalTitle;
                            setTimeout(() => printFrame.remove(), 500);
                            printFrame.contentWindow?.removeEventListener('afterprint', cleanup);
                        };

                        printFrame.contentWindow?.addEventListener('afterprint', cleanup);
                        printFrame.contentWindow?.focus();
                        printFrame.contentWindow?.print();
                        this.startReceiptReturnCountdown();
                        setTimeout(() => {
                            if (document.title === printTitle) {
                                document.title = originalTitle;
                            }
                        }, 3000);
                    }, 250);
                }
            };
        }
    </script>
@endpush
