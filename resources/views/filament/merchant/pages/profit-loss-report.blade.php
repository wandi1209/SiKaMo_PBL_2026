<x-filament-panels::page>
    @php
        $summary = $this->summary;
        $money = fn (float|int $value): string => 'Rp ' . number_format($value, 0, ',', '.');
    @endphp

    <div class="space-y-6">
        <div class="grid gap-4 md:grid-cols-2">
            <label class="space-y-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Dari Tanggal</span>
                <input type="date" wire:model.live="startDate"
                    class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </label>

            <label class="space-y-1">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Sampai Tanggal</span>
                <input type="date" wire:model.live="endDate"
                    class="w-full rounded-lg border-gray-300 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
            </label>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Omzet</p>
                <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $money($summary['revenue']) }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Modal Terjual</p>
                <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">{{ $money($summary['cost']) }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Laba Kotor</p>
                <p class="mt-2 text-2xl font-bold {{ $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                    {{ $money($summary['profit']) }}
                </p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Margin</p>
                <p class="mt-2 text-2xl font-bold text-gray-950 dark:text-white">
                    {{ number_format($summary['margin'], 2, ',', '.') }}%
                </p>
                <p class="mt-1 text-xs text-gray-500">{{ $summary['transaction_count'] }} transaksi, {{ $summary['items_sold'] }} item</p>
            </div>
        </div>

        <div class="grid gap-6 xl:grid-cols-2">
            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Laba/Rugi per Supplier</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Supplier</th>
                                <th class="px-5 py-3 text-right">Omzet</th>
                                <th class="px-5 py-3 text-right">Modal</th>
                                <th class="px-5 py-3 text-right">Laba</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($this->supplierRows as $row)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">
                                        {{ $row['supplier'] }}
                                        <span class="block text-xs font-normal text-gray-500">{{ $row['quantity'] }} item</span>
                                    </td>
                                    <td class="px-5 py-3 text-right">{{ $money($row['revenue']) }}</td>
                                    <td class="px-5 py-3 text-right">{{ $money($row['cost']) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $money($row['profit']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <div class="border-b border-gray-100 px-5 py-4 dark:border-gray-800">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Top Produk Berdasarkan Laba</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500 dark:bg-gray-950 dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Produk</th>
                                <th class="px-5 py-3 text-right">Terjual</th>
                                <th class="px-5 py-3 text-right">Omzet</th>
                                <th class="px-5 py-3 text-right">Laba</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse ($this->productRows as $row)
                                <tr>
                                    <td class="px-5 py-3 font-medium text-gray-900 dark:text-white">{{ $row['product'] }}</td>
                                    <td class="px-5 py-3 text-right">{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                                    <td class="px-5 py-3 text-right">{{ $money($row['revenue']) }}</td>
                                    <td class="px-5 py-3 text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $money($row['profit']) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-8 text-center text-gray-500">Belum ada transaksi pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>
</x-filament-panels::page>
