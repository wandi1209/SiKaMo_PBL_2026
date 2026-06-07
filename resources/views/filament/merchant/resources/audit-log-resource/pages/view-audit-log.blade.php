<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Section -->
        <x-filament::section>
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex items-start gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary-50 text-primary-700 dark:bg-primary-500/10 dark:text-primary-300">
                        <x-heroicon-o-document-magnifying-glass class="h-6 w-6" />
                    </div>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <h2 class="text-xl font-bold text-gray-950 dark:text-white">Detail Audit Log</h2>
                            <span class="rounded-md bg-gray-100 px-2 py-1 text-xs font-bold text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                #{{ $record->id }}
                            </span>
                        </div>
                        <p class="mt-1 text-sm font-medium text-gray-600 dark:text-gray-400">
                            {{ $record->merchant?->name ?? 'Merchant' }} -
                            {{ $record->created_at?->translatedFormat('d M Y, H:i:s') ?? '-' }}
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @if (filled($record->reviewed_at))
                        <span class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-emerald-50 px-4 text-sm font-bold text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-500/30">
                            <x-heroicon-m-check-circle class="h-5 w-5" />
                            Sudah ditinjau
                        </span>
                    @endif
                </div>
            </div>
        </x-filament::section>

        <!-- Flag Alert -->
        @if ($this->flagDescription())
            <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100">
                <div class="flex items-start gap-2">
                    <x-heroicon-m-exclamation-triangle class="mt-0.5 h-5 w-5 shrink-0" />
                    <span>{{ $this->flagDescription() }}</span>
                </div>
            </div>
        @endif

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 gap-6 xl:grid-cols-3">
            <!-- Sidebar: Data Details -->
            <div class="space-y-6 xl:col-span-1">
                <x-filament::section>
                    <x-slot name="heading">Detail Data</x-slot>

                    <div class="space-y-4">
                        <!-- Module Card -->
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-5 dark:border-white/10 dark:bg-gray-950">
                            <div class="flex items-center gap-4">
                                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-lg bg-white text-primary-600 shadow-sm dark:bg-gray-900 dark:text-primary-300">
                                    <x-heroicon-o-cube class="h-8 w-8" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Modul</p>
                                    <p class="mt-1 text-base font-bold text-gray-950 dark:text-white">{{ $this->modelLabel() }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Data Fields -->
                        <div class="grid gap-4">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Nama Data</p>
                                <p class="mt-1 break-words text-sm font-bold text-gray-950 dark:text-white">{{ $record->auditable_label ?: '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Dilakukan Oleh</p>
                                <div class="mt-1 flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-bold text-gray-950 dark:text-white">{{ $record->user_name ?: 'System' }}</span>
                                    <span class="rounded-md bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                                        {{ $this->roleLabel() }}
                                    </span>
                                </div>
                                @if (!empty($record->metadata['system_actor']) && !empty($record->metadata['triggered_by_user_name']))
                                    <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                        Dipicu oleh {{ $record->metadata['triggered_by_user_name'] }}
                                        @if (!empty($record->metadata['triggered_by_user_role']))
                                            ({{ Str::headline($record->metadata['triggered_by_user_role']) }})
                                        @endif
                                    </p>
                                @endif
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Jenis Aksi</p>
                                <span class="mt-1 inline-flex rounded-md bg-amber-100 px-2.5 py-1 text-xs font-bold text-amber-700 dark:bg-amber-500/15 dark:text-amber-300">
                                    {{ $this->eventLabel() }}
                                </span>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Route</p>
                                <p class="mt-1 break-words text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $record->route_name ?: '-' }}</p>
                            </div>

                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">IP</p>
                                <p class="mt-1 text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $record->ip_address ?: '-' }}</p>
                            </div>
                        </div>
                    </div>
                </x-filament::section>
            </div>

            <!-- Main Content: Data Changes -->
            <div class="space-y-6 xl:col-span-2">
                <x-filament::section>
                    <x-slot name="heading">Perubahan Data</x-slot>
                    @php
                        $oldValues = $this->changedOldValues();
                        $newValues = $this->changedNewValues();
                        $fields = collect(array_keys($oldValues))->merge(array_keys($newValues))->unique()->values();
                    @endphp
                    <x-slot name="description">{{ $fields->count() }} field tercatat pada log ini.</x-slot>

                    <div class="space-y-5">
                        <!-- Change Summary -->
                        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-gray-950">
                            <div class="flex items-start gap-3">
                                <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-white text-primary-600 shadow-sm dark:bg-gray-900 dark:text-primary-300">
                                    <x-heroicon-o-document-text class="h-5 w-5" />
                                </div>
                                <div class="min-w-0">
                                    <p class="text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-400">Ringkasan Perubahan</p>
                                    <p class="mt-1 break-words text-sm font-semibold leading-6 text-gray-800 dark:text-gray-200">
                                        {{ $record->summary ?: 'Tidak ada ringkasan khusus pada log ini.' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Changes Table -->
                        <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                            <table class="w-full divide-y divide-gray-200 text-left text-sm dark:divide-white/10">
                                <thead class="bg-gray-50 dark:bg-white/5">
                                    <tr>
                                        <th class="px-4 py-3 font-bold text-gray-700 dark:text-gray-200">Field</th>
                                        <th class="px-4 py-3 font-bold text-gray-700 dark:text-gray-200">Sebelum</th>
                                        <th class="px-4 py-3 font-bold text-gray-700 dark:text-gray-200">Sesudah</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                                    @forelse ($fields as $field)
                                        <tr>
                                            <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                                {{ $this->fieldLabel($field) }}
                                            </td>
                                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                                {{ $this->valueLabel($oldValues[$field] ?? null, $field) }}
                                            </td>
                                            <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                                {{ $this->valueLabel($newValues[$field] ?? null, $field) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-4 py-6 text-center text-gray-500 dark:text-gray-400">
                                                Tidak ada perubahan field yang tercatat.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </x-filament::section>
            </div>
        </div>

        <!-- Session Timeline -->
        @php
            $sessionLogs = $this->sessionLogs();
            $sessionLogin = $this->sessionLogin();
            $sessionLogout = $this->sessionLogout();
            $hasSessionLogs = $record->user_name && $sessionLogs->isNotEmpty();
        @endphp

        @if ($hasSessionLogs)
            <x-filament::section>
                <x-slot name="heading">Timeline Sesi User</x-slot>
                <x-slot name="description">
                    Sesi {{ $record->user_name }} dari
                    {{ $sessionLogin?->created_at?->translatedFormat('d M Y, H:i') ?? 'waktu tidak diketahui' }}
                    sampai
                    {{ $sessionLogout?->created_at?->translatedFormat('d M Y, H:i') ?? 'sekarang / belum logout' }}.
                </x-slot>

                <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                    <table class="w-full divide-y divide-gray-200 text-left text-sm dark:divide-white/10">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="w-28 px-4 py-3 font-bold text-gray-700 dark:text-gray-200">Waktu</th>
                                <th class="px-4 py-3 font-bold text-gray-700 dark:text-gray-200">Aktivitas</th>
                                <th class="px-4 py-3 font-bold text-gray-700 dark:text-gray-200">Ringkasan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-white/10 dark:bg-gray-900">
                            @foreach ($sessionLogs as $log)
                                <tr class="{{ $log->id === $record->id ? 'bg-primary-50/60 dark:bg-primary-500/10' : '' }}">
                                    <td class="px-4 py-3 font-semibold text-gray-950 dark:text-white">
                                        {{ $log->created_at?->format('H:i') ?? '-' }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="rounded-md bg-gray-100 px-2 py-0.5 text-xs font-bold text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                                {{ $this->eventLabel($log->event) }}
                                            </span>
                                            <span class="rounded-md bg-sky-100 px-2 py-0.5 text-xs font-bold text-sky-700 dark:bg-sky-500/15 dark:text-sky-300">
                                                {{ $this->modelLabel($log->auditable_type) }}
                                            </span>
                                            <span class="text-sm font-semibold text-gray-700 dark:text-gray-200">
                                                {{ $log->auditable_label ?: '-' }}
                                            </span>
                                            @if ($log->id === $record->id)
                                                <span class="rounded-md bg-primary-600 px-2 py-0.5 text-xs font-bold text-white">Log ini</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-300">
                                        {{ $log->summary ?: '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-filament::section>
        @endif

        <!-- Review Notes Section -->
        <x-filament::section>
            <x-slot name="heading">Catatan Peninjau</x-slot>
            <x-slot name="description">Catatan dapat diisi untuk semua log sebagai hasil verifikasi owner.</x-slot>

            <div class="space-y-4">
                <textarea
                    wire:model.defer="reviewNote"
                    rows="5"
                    placeholder="Ketik catatan hasil konfirmasi atau verifikasi audit di sini..."
                    class="block w-full rounded-lg border-gray-300 text-sm shadow-sm transition duration-75 placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-950 dark:text-white dark:placeholder:text-gray-500"
                ></textarea>

                <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 dark:border-white/10 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                        @if (filled($record->reviewed_at))
                            Ditinjau pada {{ $record->reviewed_at->translatedFormat('d M Y, H:i:s') }}.
                        @elseif ($this->canMarkReviewed())
                            Simpan catatan sekaligus tandai log ini sudah ditinjau.
                        @else
                            Simpan catatan tanpa mengubah status flag.
                        @endif
                    </p>

                    <div class="flex flex-col gap-2 sm:flex-row">
                        @if ($this->canMarkReviewed())
                            <button
                                type="button"
                                wire:click="markReviewed"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-500"
                            >
                                <x-heroicon-m-check-circle class="h-5 w-5" />
                                Konfirmasi & Simpan
                            </button>
                        @else
                            <button
                                type="button"
                                wire:click="saveReviewNote"
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-primary-600 px-5 text-sm font-bold text-white shadow-sm transition hover:bg-primary-500"
                            >
                                <x-heroicon-m-check class="h-5 w-5" />
                                Simpan Catatan
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
