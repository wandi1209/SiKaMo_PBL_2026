<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\StockLogResource\Pages;
use App\Models\Product;
use App\Models\StockLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockLogResource extends Resource
{
    protected static ?string $model = StockLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationLabel = 'Riwayat Stok';
    protected static ?string $modelLabel = 'Riwayat Stok';
    protected static ?string $pluralModelLabel = 'Riwayat Stok';
    protected static ?string $navigationGroup = 'Inventori';
    protected static ?int $navigationSort = 5;
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
        return parent::getEloquentQuery()
            ->with(['product.category', 'user'])
            ->whereHas('product', fn(Builder $query) => $query->where('merchant_id', filament()->getTenant()?->id));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Riwayat Stok')
                ->schema([
                    Forms\Components\TextInput::make('product.name')->label('Produk')->disabled(),
                    Forms\Components\TextInput::make('user.name')->label('User')->disabled(),
                    Forms\Components\TextInput::make('type')->label('Jenis')->disabled(),
                    Forms\Components\TextInput::make('quantity_change')->label('Perubahan')->disabled(),
                    Forms\Components\TextInput::make('stock_before')->label('Stok Sebelum')->disabled(),
                    Forms\Components\TextInput::make('stock_after')->label('Stok Sesudah')->disabled(),
                    Forms\Components\Textarea::make('note')->label('Catatan')->disabled()->columnSpanFull(),
                ])
                ->columns(2),
        ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Detail Riwayat Stok')
                    ->schema([
                        Infolists\Components\TextEntry::make('product.name')
                            ->label('Produk')
                            ->placeholder('Produk sudah dihapus'),

                        Infolists\Components\TextEntry::make('user.name')
                            ->label('User')
                            ->placeholder('System'),

                        Infolists\Components\TextEntry::make('type')
                            ->label('Jenis')
                            ->badge()
                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                'in' => 'Masuk',
                                'out' => 'Keluar',
                                'adjustment' => 'Koreksi',
                                default => filled($state) ? ucfirst($state) : '-',
                            })
                            ->color(fn (?string $state): string => match ($state) {
                                'in' => 'success',
                                'out' => 'danger',
                                'adjustment' => 'warning',
                                default => 'gray',
                            }),

                        Infolists\Components\TextEntry::make('quantity_change')
                            ->label('Perubahan')
                            ->formatStateUsing(fn (?int $state): string => $state === null
                                ? '-'
                                : ($state > 0 ? '+' : '') . number_format($state, 0, ',', '.'))
                            ->color(fn (?int $state): string => ($state ?? 0) < 0 ? 'danger' : 'success'),

                        Infolists\Components\TextEntry::make('stock_before')
                            ->label('Stok Sebelum')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('stock_after')
                            ->label('Stok Sesudah')
                            ->numeric(),

                        Infolists\Components\TextEntry::make('note')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
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

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Produk')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product.category.name')
                    ->label('Kategori')
                    ->badge()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('Jenis')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'adjustment' => 'Koreksi',
                        default => ucfirst($state),
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'in' => 'success',
                        'out' => 'danger',
                        'adjustment' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('quantity_change')
                    ->label('Perubahan')
                    ->formatStateUsing(fn(int $state): string => $state > 0 ? '+' . number_format($state, 0, ',', '.') : number_format($state, 0, ',', '.'))
                    ->color(fn(int $state): string => $state < 0 ? 'danger' : 'success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_before')
                    ->label('Sebelum')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock_after')
                    ->label('Sesudah')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->placeholder('System')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('note')
                    ->label('Catatan')
                    ->limit(48)
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_id')
                    ->label('Produk')
                    ->options(fn(): array => Product::query()
                        ->where('merchant_id', filament()->getTenant()?->id)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable(),

                Tables\Filters\SelectFilter::make('type')
                    ->label('Jenis')
                    ->options([
                        'in' => 'Masuk',
                        'out' => 'Keluar',
                        'adjustment' => 'Koreksi',
                    ]),

                Filter::make('period')
                    ->label('Periode')
                    ->form([
                        Forms\Components\Select::make('month')
                            ->label('Bulan')
                            ->options(static::monthOptions()),
                        Forms\Components\Select::make('year')
                            ->label('Tahun')
                            ->options(static::yearOptions()),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when($data['month'] ?? null, fn(Builder $query, $month): Builder => $query->whereMonth('created_at', $month))
                        ->when($data['year'] ?? null, fn(Builder $query, $year): Builder => $query->whereYear('created_at', $year))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStockLogs::route('/'),
            'view' => Pages\ViewStockLog::route('/{record}'),
        ];
    }

    private static function monthOptions(): array
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];
    }

    private static function yearOptions(): array
    {
        $currentYear = (int) now()->year;

        return collect(range($currentYear, $currentYear - 5))
            ->mapWithKeys(fn(int $year): array => [$year => (string) $year])
            ->all();
    }
}
