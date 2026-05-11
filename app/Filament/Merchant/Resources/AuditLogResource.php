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
            ->with('user')
            ->where(function (Builder $query) use ($merchantId): void {
                if (! $merchantId) {
                    $query->whereRaw('1 = 0');

                    return;
                }

                $query
                    ->where(function (Builder $query) use ($merchantId): void {
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
            Forms\Components\Section::make('Aktivitas')
                ->schema([
                    Forms\Components\TextInput::make('created_at')
                        ->label('Waktu')
                        ->formatStateUsing(fn ($state) => $state?->format('d M Y, H:i:s'))
                        ->disabled(),

                    Forms\Components\TextInput::make('user.name')
                        ->label('Oleh')
                        ->disabled(),

                    Forms\Components\TextInput::make('event')
                        ->label('Aksi')
                        ->formatStateUsing(fn (string $state): string => static::eventLabel($state))
                        ->disabled(),

                    Forms\Components\TextInput::make('auditable_type')
                        ->label('Data')
                        ->formatStateUsing(fn (string $state): string => static::modelLabel($state))
                        ->disabled(),

                    Forms\Components\TextInput::make('auditable_label')
                        ->label('Nama Data')
                        ->disabled(),

                    Forms\Components\Textarea::make('old_values')
                        ->label('Sebelum')
                        ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                        ->disabled()
                        ->rows(8),

                    Forms\Components\Textarea::make('new_values')
                        ->label('Sesudah')
                        ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                        ->disabled()
                        ->rows(8),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh')
                    ->placeholder('System')
                    ->searchable(),

                Tables\Columns\TextColumn::make('event')
                    ->label('Aksi')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => static::eventLabel($state))
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('auditable_type')
                    ->label('Data')
                    ->formatStateUsing(fn (string $state): string => static::modelLabel($state)),

                Tables\Columns\TextColumn::make('auditable_label')
                    ->label('Nama')
                    ->placeholder('-')
                    ->searchable(),

                Tables\Columns\TextColumn::make('summary')
                    ->label('Perubahan')
                    ->state(fn (AuditLog $record): string => static::summarizeChanges($record))
                    ->wrap(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->label('Aksi')
                    ->options([
                        'created' => 'Tambah',
                        'updated' => 'Update',
                        'deleted' => 'Hapus',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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

    private static function eventLabel(string $event): string
    {
        return match ($event) {
            'created' => 'Tambah',
            'updated' => 'Update',
            'deleted' => 'Hapus',
            default => ucfirst($event),
        };
    }

    private static function modelLabel(string $model): string
    {
        return match ($model) {
            Product::class => 'Produk',
            Category::class => 'Kategori',
            User::class => 'Kasir/User',
            Transaction::class => 'Transaksi',
            DailyReport::class => 'Laporan Harian',
            default => class_basename($model),
        };
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
