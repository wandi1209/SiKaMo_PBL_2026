@php
     $products = $this->getLowStockProducts();
     $count = $this->getLowStockProductsCount();
@endphp

<x-filament::section class="w-full">
     <div class="rounded-lg p-5">
          <div class="grid gap-5 lg:grid-cols-[18rem_1fr] lg:items-start">
               <div>
                    <div class="flex items-center gap-3">
                         <div
                              class="flex h-11 w-11 items-center justify-center rounded-xl bg-red-100 text-red-700 shadow-sm dark:bg-red-500/15 dark:text-red-300">
                              <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2.5"
                                   viewBox="0 0 24 24" aria-hidden="true">
                                   <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z" />
                              </svg>
                         </div>
                         <div>
                              <p class="text-sm font-black text-red-800 dark:text-red-200">Stok Menipis</p>
                              <p class="text-3xl font-black tracking-tight text-red-950 dark:text-red-100">
                                   {{ number_format($count, 0, ',', '.') }}
                              </p>
                         </div>
                    </div>
                    <p class="mt-3 text-sm font-semibold leading-6 text-red-800 dark:text-red-200">
                         Produk dengan stok di bawah batas aman tampil di sini.
                    </p>
               </div>

               <div class="min-w-0">
                    @if ($products->isEmpty())
                         <div
                              class="flex items-center gap-3 rounded-lg border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700 dark:border-emerald-900/40 dark:bg-emerald-950/20 dark:text-emerald-300">
                              <x-heroicon-o-check-circle class="h-5 w-5" />
                              Semua stok masih aman.
                         </div>
                    @else
                         <div class="grid w-full gap-3 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                              @foreach ($products as $product)
                                   <div
                                        class="w-full rounded-lg border border-red-200 bg-white px-4 py-3 shadow-sm dark:border-red-900/50 dark:bg-gray-900">
                                        <div class="flex items-start justify-between gap-3">
                                             <div class="min-w-0 flex-1">
                                                  <p
                                                       class="text-sm font-black leading-5 text-gray-950 break-words dark:text-white">
                                                       {{ $product->name }}
                                                  </p>
                                                  <p
                                                       class="mt-1 text-xs font-semibold leading-5 text-gray-600 break-words dark:text-gray-300">
                                                       {{ $product->category?->name ?? 'Tanpa kategori' }}
                                                  </p>
                                             </div>
                                             <span
                                                  class="shrink-0 rounded-md bg-red-100 px-3 py-1.5 text-xs font-black text-red-800 ring-1 ring-red-200 dark:bg-red-500/15 dark:text-red-200 dark:ring-red-500/30">
                                                  Stok: {{ number_format($product->stock, 0, ',', '.') }}
                                             </span>
                                        </div>
                                        <p class="mt-3 text-xs font-black text-red-700 dark:text-red-300">
                                             Batas aman: {{ number_format($this->getEffectiveThreshold($product), 0, ',', '.') }}
                                        </p>
                                   </div>
                              @endforeach
                         </div>

                         @if ($count > $products->count())
                              <p class="mt-3 text-xs font-black text-red-800 dark:text-red-200">
                                   +{{ number_format($count - $products->count(), 0, ',', '.') }} produk lain juga stoknya
                                   menipis.
                              </p>
                         @endif
                    @endif
               </div>
          </div>
     </div>
</x-filament::section>