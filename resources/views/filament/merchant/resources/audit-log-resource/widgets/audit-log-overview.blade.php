@php
    $tenant = filament()->getTenant();
@endphp

<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-5">
            <div class="flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-gray-950 dark:text-white">Audit Log</h2>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-300">
                        {{ $tenant?->name ?? 'Merchant' }} - Owner
                    </p>
                </div>
                <div class="flex items-center gap-2 text-sm font-semibold text-gray-500 dark:text-gray-400">
                    {{-- Gunakan ikon bawaan Filament agar tetap muncul walau Blade Icon Components dinonaktifkan --}}
                    <x-filament::icon icon="heroicon-m-clock" class="h-5 w-5 text-primary-500" />
                    <span>{{ now()->translatedFormat('d M Y') }}</span>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                <div class="h-full rounded-lg border border-emerald-200 bg-emerald-50 p-4 shadow-sm dark:border-emerald-500/30 dark:bg-emerald-500/10">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-3xl font-bold tracking-tight text-emerald-700 dark:text-emerald-300">
                                {{ number_format($this->getTotalToday(), 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-sm font-semibold text-emerald-950 dark:text-emerald-100">Total log hari ini</p>
                        </div>
                        <div class="shrink-0 rounded-lg bg-white/80 p-2 text-emerald-700 shadow-sm ring-1 ring-black/5 dark:bg-white/5 dark:text-emerald-200 dark:ring-white/10">
                            <x-filament::icon icon="heroicon-m-clipboard-document-list" class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="h-full rounded-lg border border-amber-200 bg-amber-50 p-4 shadow-sm dark:border-amber-500/30 dark:bg-amber-500/10">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-3xl font-bold tracking-tight text-amber-700 dark:text-amber-300">
                                {{ number_format($this->getPendingFlags(), 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-sm font-semibold text-amber-950 dark:text-amber-100">Flag belum ditinjau</p>
                        </div>
                        <div class="shrink-0 rounded-lg bg-white/80 p-2 text-amber-700 shadow-sm ring-1 ring-black/5 dark:bg-white/5 dark:text-amber-200 dark:ring-white/10">
                            <x-filament::icon icon="heroicon-m-exclamation-triangle" class="h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div class="h-full rounded-lg border border-sky-200 bg-sky-50 p-4 shadow-sm dark:border-sky-500/30 dark:bg-sky-500/10">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-3xl font-bold tracking-tight text-sky-700 dark:text-sky-300">
                                {{ number_format($this->getActiveUsers(), 0, ',', '.') }}
                            </p>
                            <p class="mt-1 text-sm font-semibold text-sky-950 dark:text-sky-100">User aktif hari ini</p>
                        </div>
                        <div class="shrink-0 rounded-lg bg-white/80 p-2 text-sky-700 shadow-sm ring-1 ring-black/5 dark:bg-white/5 dark:text-sky-200 dark:ring-white/10">
                            <x-filament::icon icon="heroicon-m-user-group" class="h-6 w-6" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
