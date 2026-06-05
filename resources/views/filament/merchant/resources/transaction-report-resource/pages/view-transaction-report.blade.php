<x-filament-panels::page>
    @php
        $transaction = $this->record->loadMissing(['user', 'refundedBy', 'items.product.category']);
        $merchant = filament()->getTenant();
        $items = $transaction->items;
        $soldQuantity = $items->sum('quantity');
        $subtotal = $items->sum('subtotal');

        $rupiah = fn ($value): string => 'Rp ' . number_format((float) $value, 0, ',', '.');
        $paymentMethod = strtoupper((string) ($transaction->payment_method ?? '-'));
        $status = strtoupper((string) ($transaction->status ?? '-'));
        $statusColor = $transaction->status === 'refunded' ? 'danger' : 'success';
        $statusLabel = $transaction->status === 'refunded' ? 'Refund' : 'Selesai';
        $transactionTime = $transaction->created_at?->translatedFormat('d F Y, H:i') ?? '-';
        $refundedAt = $transaction->refunded_at?->translatedFormat('d F Y, H:i');
    @endphp

    <style>
        .sikamo-sales-summary {
            display: grid;
            gap: 1.25rem;
        }

        .sikamo-sales-header {
            display: grid;
            gap: 1rem;
        }

        .sikamo-sales-total-card,
        .sikamo-sales-stat-card {
            border: 1px solid #e2e8f0;
            border-radius: 0.75rem;
            padding: 1rem;
        }

        .sikamo-sales-total-card {
            background: #f0fdf4;
            border-color: #bbf7d0;
        }

        .sikamo-sales-stat-grid {
            display: grid;
            gap: 0.875rem;
        }

        .sikamo-sales-stat-card {
            background: #f8fafc;
            min-height: 6rem;
        }

        .sikamo-sales-label {
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 600;
            line-height: 1.25rem;
        }

        .sikamo-sales-value {
            color: #020617;
            font-size: 1.125rem;
            font-weight: 700;
            line-height: 1.75rem;
            margin-top: 0.625rem;
        }

        .sikamo-sales-total-value {
            color: #166534;
            font-size: 1.75rem;
            font-weight: 800;
            line-height: 2.25rem;
            margin-top: 0.625rem;
        }

        .sikamo-sales-status-pill {
            align-items: center;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 9999px;
            color: #4d7c0f;
            display: inline-flex;
            font-size: 0.75rem;
            font-weight: 700;
            line-height: 1rem;
            padding: 0.25rem 0.625rem;
            width: fit-content;
        }

        .sikamo-sales-status-pill.is-refunded {
            background: #fef2f2;
            border-color: #fecaca;
            color: #b91c1c;
        }

        @media (min-width: 768px) {
            .sikamo-sales-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (min-width: 1280px) {
            .sikamo-sales-header {
                grid-template-columns: minmax(0, 1fr) 20rem;
                align-items: stretch;
            }

            .sikamo-sales-stat-grid {
                grid-template-columns: repeat(5, minmax(0, 1fr));
            }
        }

        @media print {
            * {
                background: transparent !important;
                color: #000 !important;
                box-shadow: none !important;
                text-shadow: none !important;
            }

            .fi-sidebar,
            .fi-topbar,
            .fi-breadcrumbs,
            .fi-header-actions {
                display: none !important;
            }

            .fi-main {
                padding: 0 !important;
                margin: 0 !important;
            }

            body {
                margin: 0;
                padding: 10mm;
                line-height: 1.5;
            }

            .space-y-6 {
                display: block;
            }

            .sikamo-sales-total-card,
            .sikamo-sales-stat-card {
                border: 1px solid #000 !important;
                background: transparent !important;
                page-break-inside: avoid;
            }

            table {
                border-collapse: collapse;
                width: 100%;
                page-break-inside: auto;
            }

            tr {
                page-break-inside: avoid;
                page-break-after: auto;
            }

            thead {
                display: table-header-group;
            }

            tfoot {
                display: table-footer-group;
            }

            .grid {
                display: block !important;
            }

            /* Ensure text is visible */
            a {
                color: #000 !important;
                text-decoration: underline;
            }

            /* Ensure proper spacing */
            x-filament--section {
                page-break-inside: avoid;
                margin-bottom: 20px;
            }

            /* Hide dark mode styling */
            .dark\:text-white,
            .dark\:text-gray-400,
            .dark\:text-gray-300,
            .dark\:bg-white\/5 {
                color: #000 !important;
                background: transparent !important;
            }
        }
    </style>

    <div class="space-y-6">
        <x-filament::section>
            <div class="sikamo-sales-summary">
                <div class="sikamo-sales-header">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Invoice Penjualan</p>
                        <div class="mt-1 flex flex-wrap items-center gap-3">
                            <h2 class="text-2xl font-bold tracking-tight text-gray-950 dark:text-white">
                                {{ $transaction->invoice_number }}
                            </h2>
                            <x-filament::badge :color="$statusColor">
                                {{ $statusLabel }}
                            </x-filament::badge>
                        </div>
                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                            {{ $merchant?->name ?? 'Toko' }} - {{ $transactionTime }}
                        </p>
                    </div>

                    <div class="sikamo-sales-total-card">
                        <p class="sikamo-sales-label">Total Transaksi</p>
                        <p class="sikamo-sales-total-value">
                            {{ $rupiah($transaction->total_amount) }}
                        </p>
                    </div>
                </div>

                <div class="sikamo-sales-stat-grid">
                    <div class="sikamo-sales-stat-card">
                        <p class="sikamo-sales-label">Dibayar</p>
                        <p class="sikamo-sales-value">{{ $rupiah($transaction->amount_paid) }}</p>
                    </div>

                    <div class="sikamo-sales-stat-card">
                        <p class="sikamo-sales-label">Kembalian</p>
                        <p class="sikamo-sales-value">{{ $rupiah($transaction->change_amount) }}</p>
                    </div>

                    <div class="sikamo-sales-stat-card">
                        <p class="sikamo-sales-label">Item Terjual</p>
                        <p class="sikamo-sales-value">{{ number_format($soldQuantity, 0, ',', '.') }} pcs</p>
                    </div>

                    <div class="sikamo-sales-stat-card">
                        <p class="sikamo-sales-label">Metode</p>
                        <p class="sikamo-sales-value">{{ $paymentMethod }}</p>
                    </div>

                    <div class="sikamo-sales-stat-card">
                        <p class="sikamo-sales-label">Status</p>
                        <div style="margin-top: 0.75rem;">
                            <span class="sikamo-sales-status-pill {{ $transaction->status === 'refunded' ? 'is-refunded' : '' }}">
                                {{ $status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </x-filament::section>

        <div class="grid gap-6 lg:grid-cols-3 lg:items-start">
            <div class="lg:col-span-2">
                <x-filament::section>
                    <x-slot name="heading">
                        Rincian Item
                    </x-slot>

                    <x-slot name="description">
                        Produk yang masuk dalam transaksi ini.
                    </x-slot>

                    <div class="-mx-6 -mb-6 overflow-x-auto">
                        <table class="w-full min-w-[640px] divide-y divide-gray-200 text-sm dark:divide-white/10">
                            <thead class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Produk</th>
                                    <th class="px-6 py-3 text-left font-medium text-gray-500 dark:text-gray-400">Kategori</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Qty</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Harga</th>
                                    <th class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 bg-white dark:divide-white/10 dark:bg-transparent">
                                @forelse ($items as $item)
                                    <tr>
                                        <td class="px-6 py-4">
                                            <p class="font-medium text-gray-950 dark:text-white">{{ $item->product?->name ?? 'Produk terhapus' }}</p>
                                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $item->product?->barcode ?? 'Tanpa barcode' }}</p>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-300">
                                            {{ $item->product?->category?->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-medium text-gray-950 dark:text-white">
                                            {{ number_format((float) $item->quantity, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-300">
                                            {{ $rupiah($item->unit_price) }}
                                        </td>
                                        <td class="px-6 py-4 text-right font-semibold text-gray-950 dark:text-white">
                                            {{ $rupiah($item->subtotal) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500 dark:text-gray-400">
                                            Tidak ada item pada transaksi ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-white/5">
                                <tr>
                                    <td colspan="4" class="px-6 py-3 text-right font-medium text-gray-500 dark:text-gray-400">Subtotal item</td>
                                    <td class="px-6 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ $rupiah($subtotal) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-right text-base font-semibold text-gray-950 dark:text-white">Total akhir</td>
                                    <td class="px-6 py-4 text-right text-base font-bold text-emerald-700 dark:text-emerald-300">{{ $rupiah($transaction->total_amount) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-filament::section>
            </div>

            <div class="space-y-6">
                <x-filament::section>
                    <x-slot name="heading">
                        Info Transaksi
                    </x-slot>

                    <dl class="space-y-4 text-sm">
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Kasir</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $transaction->user?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Waktu</dt>
                            <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $transactionTime }}</dd>
                        </div>
                        <div class="flex items-start justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Status</dt>
                            <dd>
                                <x-filament::badge :color="$statusColor">
                                    {{ $status }}
                                </x-filament::badge>
                            </dd>
                        </div>
                        @if ($transaction->status === 'refunded')
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Direfund oleh</dt>
                                <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $transaction->refundedBy?->name ?? '-' }}</dd>
                            </div>
                            <div class="flex items-start justify-between gap-4">
                                <dt class="text-gray-500 dark:text-gray-400">Waktu refund</dt>
                                <dd class="text-right font-medium text-gray-950 dark:text-white">{{ $refundedAt ?? '-' }}</dd>
                            </div>
                            <div class="border-t border-gray-200 pt-4 dark:border-white/10">
                                <dt class="text-gray-500 dark:text-gray-400">Alasan refund</dt>
                                <dd class="mt-1 text-sm font-medium text-gray-950 dark:text-white">
                                    {{ $transaction->refund_reason ?: '-' }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </x-filament::section>

                <x-filament::section>
                    <x-slot name="heading">
                        Pembayaran
                    </x-slot>

                    <dl class="space-y-4 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Metode</dt>
                            <dd class="font-medium text-gray-950 dark:text-white">{{ $paymentMethod }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Total</dt>
                            <dd class="font-medium text-gray-950 dark:text-white">{{ $rupiah($transaction->total_amount) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-gray-500 dark:text-gray-400">Dibayar</dt>
                            <dd class="font-medium text-gray-950 dark:text-white">{{ $rupiah($transaction->amount_paid) }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4 border-t border-gray-200 pt-4 dark:border-white/10">
                            <dt class="font-medium text-gray-950 dark:text-white">Kembalian</dt>
                            <dd class="font-semibold text-gray-950 dark:text-white">{{ $rupiah($transaction->change_amount) }}</dd>
                        </div>
                    </dl>
                </x-filament::section>
            </div>
        </div>
    </div>
</x-filament-panels::page>
