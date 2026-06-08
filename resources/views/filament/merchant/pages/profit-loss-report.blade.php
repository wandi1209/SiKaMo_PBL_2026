<x-filament-panels::page>
    @php
        $summary = $this->summary;
        $money = fn (float|int $value): string => 'Rp ' . number_format($value, 0, ',', '.');
    @endphp

    <div class="space-y-6">
        <x-filament::section>
            {{ $this->form }}
        </x-filament::section>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <x-filament::section>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Omzet</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $money($summary['revenue']) }}</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Modal Terjual</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">{{ $money($summary['cost']) }}</p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Laba Kotor</p>
                    <p class="text-2xl font-bold {{ $summary['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                        {{ $money($summary['profit']) }}
                    </p>
                </div>
            </x-filament::section>

            <x-filament::section>
                <div class="space-y-2">
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Margin</p>
                    <p class="text-2xl font-bold text-gray-950 dark:text-white">
                        {{ number_format($summary['margin'], 2, ',', '.') }}%
                    </p>
                    <p class="text-xs text-gray-500">{{ $summary['transaction_count'] }} transaksi, {{ $summary['items_sold'] }} item</p>
                </div>
            </x-filament::section>
        </div>

        <x-filament::section heading="Laba/Rugi per Supplier">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800">
                            <th class="py-3 pr-4">Supplier</th>
                            <th class="px-4 py-3 text-right">Omzet</th>
                            <th class="px-4 py-3 text-right">Modal</th>
                            <th class="py-3 pl-4 text-right">Laba</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($this->supplierRows as $row)
                            <tr>
                                <td class="py-4 pr-4">
                                    <p class="font-semibold text-gray-950 dark:text-white">{{ $row['supplier'] }}</p>
                                    <p class="text-xs text-gray-500">{{ $row['quantity'] }} item</p>
                                </td>
                                <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-gray-100">{{ $money($row['revenue']) }}</td>
                                <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-gray-100">{{ $money($row['cost']) }}</td>
                                <td class="py-4 pl-4 text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $money($row['profit']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">Belum ada transaksi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>

        <x-filament::section heading="Top Produk Berdasarkan Laba">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:border-gray-800">
                            <th class="py-3 pr-4">Produk</th>
                            <th class="px-4 py-3 text-right">Terjual</th>
                            <th class="px-4 py-3 text-right">Omzet</th>
                            <th class="py-3 pl-4 text-right">Laba</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse ($this->productRows as $row)
                            <tr>
                                <td class="py-4 pr-4 font-semibold text-gray-950 dark:text-white">{{ $row['product'] }}</td>
                                <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-gray-100">{{ number_format($row['quantity'], 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-right font-medium text-gray-900 dark:text-gray-100">{{ $money($row['revenue']) }}</td>
                                <td class="py-4 pl-4 text-right font-semibold {{ $row['profit'] >= 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $money($row['profit']) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-gray-500">Belum ada transaksi pada periode ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
