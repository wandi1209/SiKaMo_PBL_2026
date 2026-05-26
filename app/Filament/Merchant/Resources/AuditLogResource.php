<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Audit Log';
    protected static ?string $modelLabel = 'Audit Log';
    protected static ?string $pluralModelLabel = 'Audit Log';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?int $navigationSort = 6;
    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'owner';
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::flaggedQuery(static::getEloquentQuery());

        if (Schema::hasColumn('audit_logs', 'reviewed_at')) {
            $query->whereNull('reviewed_at');
        }

        $count = $query->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'warning';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        $merchantId = filament()->getTenant()?->id;

        return parent::getEloquentQuery()
            ->with(['user', 'merchant'])
            ->where(function (Builder $query) use ($merchantId): void {
                if (! $merchantId) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query
                    ->where('merchant_id', $merchantId)
                    ->orWhere(function (Builder $query) use ($merchantId): void {
                        $query->where('auditable_type', \App\Models\Merchant::class)
                            ->where('auditable_id', $merchantId);
                    })
                    ->orWhere(function (Builder $query) use ($merchantId): void {
                        $query->where('auditable_type', Product::class)
                            ->whereIn('auditable_id', Product::query()->select('id')->where('merchant_id', $merchantId));
                    })
                    ->orWhere(function (Builder $query) use ($merchantId): void {
                        $query->where('auditable_type', Category::class)
                            ->whereIn('auditable_id', Category::query()->select('id')->where('merchant_id', $merchantId));
                    })
                    ->orWhere(function (Builder $query) use ($merchantId): void {
                        $query->where('auditable_type', User::class)
                            ->whereIn('auditable_id', User::query()->select('id')->where('merchant_id', $merchantId));
                    })
                    ->orWhere(function (Builder $query) use ($merchantId): void {
                        $query->where('auditable_type', DailyReport::class)
                            ->whereIn('auditable_id', DailyReport::query()->select('id')->where('merchant_id', $merchantId));
                    })
                    ->orWhere(function (Builder $query) use ($merchantId): void {
                        $query->where('auditable_type', Transaction::class)
                            ->whereIn('auditable_id', static::merchantTransactionIds($merchantId));
                    });
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Aksi')
                ->schema([
                    Forms\Components\Placeholder::make('created_at_display')
                        ->label('Waktu')
                        ->content(fn (AuditLog $record): string => $record->created_at?->translatedFormat('d M Y, H:i:s') ?? '-'),

                    Forms\Components\Placeholder::make('user_display')
                        ->label('Dilakukan oleh')
                        ->content(fn (AuditLog $record): string => trim(($record->user_name ?: 'System') . ' (' . static::roleLabel($record->user_role) . ')')),

                    Forms\Components\Placeholder::make('event_display')
                        ->label('Jenis aksi')
                        ->content(fn (AuditLog $record): string => static::eventLabel($record->event)),

                    Forms\Components\Placeholder::make('module_display')
                        ->label('Modul')
                        ->content(fn (AuditLog $record): string => static::modelLabel($record->auditable_type) . ' - ' . ($record->auditable_label ?: '-')),

                    Forms\Components\Placeholder::make('flag_display')
                        ->label('Keterangan flag')
                        ->content(fn (AuditLog $record): string => static::flagDescription($record) ?: 'Tidak ada flag luar jam.')
                        ->columnSpanFull(),

                    Forms\Components\Placeholder::make('merchant_display')
                        ->label('Merchant')
                        ->content(fn (AuditLog $record): string => $record->merchant?->name ?? '-'),

                    Forms\Components\Placeholder::make('changed_fields_count')
                        ->label('Jumlah field berubah')
                        ->content(fn (AuditLog $record): string => sprintf('%d field diubah', count($record->changed_fields ?? []))),
                ])
                ->columns(2),

            Forms\Components\Section::make('Perubahan Data')
                ->schema([
                    Forms\Components\KeyValue::make('old_values')
                        ->label('Sebelum')
                        ->formatStateUsing(fn ($state, AuditLog $record): array => static::onlyChangedValues($record, (array) ($record->old_values ?? [])))
                        ->disabled()
                        ->columnSpan(1),

                    Forms\Components\KeyValue::make('new_values')
                        ->label('Sesudah')
                        ->formatStateUsing(fn ($state, AuditLog $record): array => static::onlyChangedValues($record, (array) ($record->new_values ?? [])))
                        ->disabled()
                        ->columnSpan(1),

                    Forms\Components\Textarea::make('summary')
                        ->label('Ringkasan')
                        ->formatStateUsing(fn ($state, AuditLog $record): string => $state ?: static::summarizeChanges($record))
                        ->disabled()
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Konteks Request')
                ->schema([
                    Forms\Components\TextInput::make('request_method')->label('Method')->disabled(),
                    Forms\Components\TextInput::make('route_name')->label('Route')->disabled(),
                    Forms\Components\TextInput::make('panel')->label('Panel')->disabled(),
                    Forms\Components\TextInput::make('ip_address')->label('IP')->disabled(),
                    Forms\Components\TextInput::make('request_id')->label('Request ID')->disabled(),
                    Forms\Components\TextInput::make('user_agent')
                        ->label('User Agent')
                        ->disabled()
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('request_url')
                        ->label('URL')
                        ->disabled()
                        ->rows(2)
                        ->columnSpanFull(),
                    Forms\Components\Textarea::make('metadata')
                        ->label('Metadata')
                        ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                        ->disabled()
                        ->rows(8)
                        ->columnSpanFull(),
                ])
                ->columns(2),

            Forms\Components\Section::make('Catatan Peninjau')
                ->schema([
                    Forms\Components\Textarea::make('review_note')
                        ->label('Catatan')
                        ->disabled()
                        ->rows(4)
                        ->placeholder('Belum ada catatan peninjau.')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar log')
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M, H:i', config('app.timezone'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('user_name')
                    ->label('Nama user')
                    ->placeholder('System')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('user_role')
                    ->label('Role')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::roleLabel($state))
                    ->color(fn (?string $state): string => match ($state) {
                        'owner' => 'success',
                        'cashier' => 'info',
                        'admin' => 'primary',
                        null, '', 'system' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Jenis aksi')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::eventLabel($state))
                    ->color(fn (?string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        'login' => 'info',
                        'logout' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Modul')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => static::modelLabel($state))
                    ->color(fn (?string $state): string => match ($state) {
                        Product::class => 'info',
                        Category::class => 'info',
                        User::class => 'info',
                        Transaction::class => 'primary',
                        DailyReport::class => 'warning',
                        \App\Models\Merchant::class => 'primary',
                        null, '' => 'gray',
                        default => 'gray',
                    })
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('auditable_label')
                    ->label('Nama data')
                    ->placeholder('-')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\TextColumn::make('summary')
                    ->label('Ringkasan singkat')
                    ->state(fn (AuditLog $record): string => $record->summary ?: static::summarizeChanges($record))
                    ->wrap()
                    ->searchable(),

                Tables\Columns\IconColumn::make('flag')
                    ->label('Flag luar jam')
                    ->state(fn (AuditLog $record): bool => static::isFlagged($record))
                    ->boolean()
                    ->trueIcon('heroicon-o-exclamation-triangle')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor(fn (AuditLog $record): string => (Schema::hasColumn('audit_logs', 'reviewed_at') && filled($record->reviewed_at)) ? 'success' : 'warning')
                    ->falseColor('gray'),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Tambah',
                        'updated' => 'Update',
                        'deleted' => 'Hapus',
                        'login' => 'Login',
                        'logout' => 'Logout',
                    ]),
                Tables\Filters\SelectFilter::make('user_name')
                    ->label('User')
                    ->options(fn (): array => static::getEloquentQuery()
                        ->whereNotNull('user_name')
                        ->distinct()
                        ->orderBy('user_name')
                        ->pluck('user_name', 'user_name')
                        ->all()),
                Tables\Filters\SelectFilter::make('user_role')
                    ->label('Role')
                    ->options([
                        'owner' => 'Owner',
                        'cashier' => 'Kasir',
                        'admin' => 'Admin',
                    ]),
                Tables\Filters\SelectFilter::make('auditable_type')
                    ->label('Modul')
                    ->options([
                        Product::class => 'Produk',
                        Category::class => 'Kategori',
                        User::class => 'Kasir/User',
                        Transaction::class => 'Transaksi',
                        DailyReport::class => 'Laporan Harian',
                    ]),
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari tanggal'),
                        Forms\Components\DatePicker::make('until')->label('Sampai tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('created_at', '<=', $date))),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(fn (): StreamedResponse => static::exportCsv()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Lihat')
                    ->icon('heroicon-m-eye')
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view' => Pages\ViewAuditLog::route('/{record}'),
        ];
    }

    private static function merchantTransactionIds(int $merchantId)
    {
        return DB::table('t_transactions')
            ->select('t_transactions.id')
            ->join('t_transaction_items', 't_transaction_items.transaction_id', '=', 't_transactions.id')
            ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
            ->where('m_products.merchant_id', $merchantId)
            ->groupBy('t_transactions.id');
    }

    private static function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Waktu', 'User', 'Role', 'Aksi', 'Modul', 'Data', 'Ringkasan', 'Flag', 'IP']);

            static::getEloquentQuery()
                ->latest()
                ->chunk(200, function ($logs) use ($handle): void {
                    foreach ($logs as $log) {
                        fputcsv($handle, [
                            $log->created_at?->format('Y-m-d H:i:s'),
                            $log->user_name ?: 'System',
                            static::roleLabel($log->user_role),
                            static::eventLabel($log->event),
                            static::modelLabel($log->auditable_type),
                            $log->auditable_label,
                            $log->summary ?: static::summarizeChanges($log),
                            static::isFlagged($log) ? 'Ya' : 'Tidak',
                            $log->ip_address,
                        ]);
                    }
                });

            fclose($handle);
        }, 'audit-log.csv');
    }

    private static function userColumnHtml(AuditLog $record): string
    {
        $name = e($record->user_name ?: 'System');
        $role = e(static::roleLabel($record->user_role));
        $class = static::badgeClass($record->user_role ?: 'system');

        return <<<HTML
            <div class="space-y-1.5">
                <div class="font-semibold text-gray-950 dark:text-white">{$name}</div>
                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 {$class}">{$role}</span>
            </div>
        HTML;
    }

    private static function moduleColumnHtml(AuditLog $record): string
    {
        $module = e(static::modelLabel($record->auditable_type));
        $label = e($record->auditable_label ?: '-');
        $class = static::badgeClass($record->auditable_type ?: 'default');

        return <<<HTML
            <div class="space-y-1.5">
                <span class="inline-flex rounded-md px-2 py-0.5 text-xs font-semibold ring-1 {$class}">{$module}</span>
                <div class="break-words text-sm font-medium text-gray-600 dark:text-gray-300">{$label}</div>
            </div>
        HTML;
    }

    private static function badgeClass(?string $key): string
    {
        return match ($key) {
            'owner' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20 dark:bg-emerald-400/10 dark:text-emerald-300 dark:ring-emerald-400/30',
            'cashier' => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
            'admin' => 'bg-purple-50 text-purple-700 ring-purple-600/20 dark:bg-purple-400/10 dark:text-purple-300 dark:ring-purple-400/30',
            'system' => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-300 dark:ring-gray-400/30',
            Product::class => 'bg-blue-50 text-blue-700 ring-blue-600/20 dark:bg-blue-400/10 dark:text-blue-300 dark:ring-blue-400/30',
            Category::class => 'bg-teal-50 text-teal-700 ring-teal-600/20 dark:bg-teal-400/10 dark:text-teal-300 dark:ring-teal-400/30',
            User::class => 'bg-sky-50 text-sky-700 ring-sky-600/20 dark:bg-sky-400/10 dark:text-sky-300 dark:ring-sky-400/30',
            Transaction::class => 'bg-indigo-50 text-indigo-700 ring-indigo-600/20 dark:bg-indigo-400/10 dark:text-indigo-300 dark:ring-indigo-400/30',
            DailyReport::class => 'bg-orange-50 text-orange-700 ring-orange-600/20 dark:bg-orange-400/10 dark:text-orange-300 dark:ring-orange-400/30',
            default => 'bg-gray-50 text-gray-700 ring-gray-600/20 dark:bg-gray-400/10 dark:text-gray-300 dark:ring-gray-400/30',
        };
    }

    private static function eventLabel(?string $event): string
    {
        return match ($event) {
            'created' => 'Tambah',
            'updated' => 'Update',
            'deleted' => 'Hapus',
            'login' => 'Login',
            'logout' => 'Logout',
            null, '' => '-',
            default => Str::headline($event),
        };
    }

    private static function roleLabel(?string $role): string
    {
        return match ($role) {
            'cashier' => 'Kasir',
            'owner' => 'Owner',
            'admin' => 'Admin',
            null, '' => 'System',
            default => Str::headline($role),
        };
    }

    private static function modelLabel(?string $model): string
    {
        return match ($model) {
            Product::class => 'Produk',
            Category::class => 'Kategori',
            User::class => 'Kasir/User',
            Transaction::class => 'Transaksi',
            DailyReport::class => 'Laporan Harian',
            null, '' => '-',
            default => class_basename($model),
        };
    }

    public static function isFlagged(AuditLog $record): bool
    {
        if (($record->user_role ?? null) !== 'cashier' || ! $record->created_at) {
            return false;
        }

        [$start, $end] = static::operationalHours($record);

        $time = $record->created_at->format('H:i:s');

        return ($time < $start) || ($time > $end);
    }

    public static function flaggedQuery(Builder $query): Builder
    {
        return $query
            ->where('user_role', 'cashier')
            ->where(function (Builder $query): void {
                [$start, $end] = static::operationalHours();

                $query
                    ->whereTime('created_at', '<', $start)
                    ->orWhereTime('created_at', '>', $end);
            });
    }

    public static function flagDescription(AuditLog $record): ?string
    {
        if (! static::isFlagged($record)) {
            return null;
        }

        [$start, $end] = static::operationalHours($record);

        $time = $record->created_at?->format('H:i') ?? '-';

        $startLabel = str_replace(':', '.', substr($start, 0, 5));
        $endLabel = str_replace(':', '.', substr($end, 0, 5));

        return "Aksi dilakukan pukul {$time}, di luar jam operasional toko ({$startLabel}-{$endLabel} WIB).";
    }

    /**
     * Mengambil jam operasional merchant untuk kebutuhan flag "luar jam kerja".
     *
     * - Prioritas: $record->merchant → tenant aktif (filament()->getTenant())
     * - Fallback default: 08:00–21:00
     *
     * @return array{0:string,1:string} [start, end] format H:i:s
     */
    private static function operationalHours(?AuditLog $record = null): array
    {
        $merchant = $record?->merchant ?? filament()->getTenant();

        $start = (string) ($merchant?->operational_start_time ?: '08:00:00');
        $end = (string) ($merchant?->operational_end_time ?: '21:00:00');

        return [static::normalizeTime($start), static::normalizeTime($end)];
    }

    private static function normalizeTime(string $time): string
    {
        $time = trim($time);

        // Jika tersimpan sebagai H:i, tambahkan detik agar konsisten untuk perbandingan string/time.
        if (preg_match('/^\d{2}:\d{2}$/', $time) === 1) {
            return $time . ':00';
        }

        return $time;
    }

    private static function onlyChangedValues(AuditLog $record, array $values): array
    {
        $fields = $record->changed_fields ?? [];

        if (! is_array($fields) || $fields === []) {
            return $values;
        }

        return collect($fields)
            ->mapWithKeys(fn (string $field): array => [$field => $values[$field] ?? null])
            ->all();
    }

    private static function referenceLabel(AuditLog $record): string
    {
        if ($record->auditable_type === Transaction::class && $record->auditable_id) {
            return 'Transaksi #' . $record->auditable_id;
        }

        return $record->request_id
            ? 'Request ' . $record->request_id
            : '-';
    }

    private static function timelineLabel(AuditLog $record): string
    {
        $parts = collect([
            $record->user_name ?: 'System',
            static::eventLabel($record->event),
            $record->route_name ?: $record->request_method,
        ])->filter();

        return $parts->isEmpty() ? '-' : $parts->implode(' -> ');
    }

    private static function summarizeChanges(AuditLog $record): string
    {
        $old = $record->old_values ?? [];
        $new = $record->new_values ?? [];

        if ($record->event === 'created') {
            return 'Data baru ditambahkan.';
        }

        if ($record->event === 'deleted') {
            return 'Data dihapus.';
        }

        if (array_key_exists('stock', $new)) {
            $oldStock = $old['stock'] ?? '-';
            $newStock = $new['stock'];

            return "Stok berubah dari {$oldStock} menjadi {$newStock}.";
        }

        $changedFields = collect(array_keys($new))
            ->reject(fn (string $field): bool => in_array($field, ['updated_at'], true))
            ->map(fn (string $field): string => str_replace('_', ' ', $field))
            ->take(4)
            ->implode(', ');

        return $changedFields !== ''
            ? 'Mengubah: ' . $changedFields
            : 'Tidak ada perubahan penting.';
    }
}
