<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\TransactionReportResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Carbon\Carbon;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionReportResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Laporan Penjualan';
    protected static ?string $modelLabel = 'Laporan Penjualan';
    protected static ?string $pluralModelLabel = 'Laporan Penjualan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?int $navigationSort = 5;
    protected static bool $isScopedToTenant = false;

    public static function canViewAny(): bool
    {
        return auth()->user()?->role === 'owner' || auth()->user()?->role === 'cashier';
    }

    public static function canView($record): bool
    {
        return auth()->user()?->role === 'owner' || auth()->user()?->role === 'cashier';
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

        $query = parent::getEloquentQuery()
            ->with(['user', 'refundedBy', 'member', 'items.product.category'])
            ->whereIn('status', ['completed', 'refunded'])
            ->whereHas('items.product', fn(Builder $query) => $query->where('merchant_id', $merchantId));

        if (auth()->user()?->role === 'cashier') {
            $query->whereDate('created_at', Carbon::today());
        }

        return $query;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Transaksi')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')->label('Invoice')->disabled(),
                    Forms\Components\TextInput::make('user.name')->label('Kasir')->disabled(),
                    Forms\Components\TextInput::make('member_name')->label('Member')->disabled(),
                    Forms\Components\TextInput::make('member_phone')->label('Nomor HP Member')->disabled(),
                    Forms\Components\TextInput::make('payment_method')->label('Metode Pembayaran')->disabled(),
                    Forms\Components\TextInput::make('subtotal_amount')->label('Subtotal')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('discount_amount')->label('Diskon')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('member_discount_amount')->label('Diskon Member')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('total_amount')->label('Total Bayar')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('amount_paid')->label('Dibayar')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('change_amount')->label('Kembalian')->prefix('Rp')->disabled(),
                    Forms\Components\Placeholder::make('items_summary')
                        ->label('Item')
                        ->content(fn(Transaction $record): string => $record->items
                            ->map(fn($item): string => "{$item->product?->name} x{$item->quantity} = Rp " . number_format($item->subtotal, 0, ',', '.'))
                            ->implode("\n")),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('member_name')
                    ->label('Member')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Item')
                    ->counts('items')
                    ->badge(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => strtoupper((string) $state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => match ($state) {
                        'completed' => 'Selesai',
                        'refunded' => 'Refund',
                        default => ucfirst((string) $state),
                    })
                    ->color(fn(?string $state): string => match ($state) {
                        'completed' => 'success',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total Bayar')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Diskon')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('member_discount_amount')
                    ->label('Diskon Member')
                    ->money('IDR')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
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

                Tables\Filters\SelectFilter::make('payment_method')
                    ->label('Metode Pembayaran')
                    ->options([
                        'cash' => 'Cash',
                        'qris' => 'QRIS',
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                        'transfer' => 'Transfer',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'completed' => 'Selesai',
                        'refunded' => 'Refund',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn(): StreamedResponse => static::exportCsv()),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    private static function exportCsv(): StreamedResponse
    {
        $merchantId = filament()->getTenant()?->id;

        return response()->streamDownload(function () use ($merchantId): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Invoice', 'Waktu', 'Kasir', 'Member', 'Nomor HP Member', 'Metode Pembayaran', 'Subtotal', 'Diskon', 'Diskon Member', 'Total', 'Item']);

            static::getEloquentQuery()
                ->whereHas('items.product', fn(Builder $query) => $query->where('merchant_id', $merchantId))
                ->latest()
                ->chunk(200, function ($transactions) use ($handle): void {
                    foreach ($transactions as $transaction) {
                        fputcsv($handle, [
                            $transaction->invoice_number,
                            $transaction->created_at?->format('Y-m-d H:i:s'),
                            $transaction->user?->name,
                            $transaction->member_name,
                            $transaction->member_phone,
                            $transaction->payment_method,
                            $transaction->subtotal_amount,
                            $transaction->discount_amount,
                            $transaction->member_discount_amount,
                            $transaction->total_amount,
                            $transaction->items
                                ->map(fn($item): string => "{$item->product?->name} x{$item->quantity}")
                                ->implode('; '),
                        ]);
                    }
                });

            fclose($handle);
        }, 'laporan-penjualan.csv');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactionReports::route('/'),
            'view' => Pages\ViewTransactionReport::route('/{record}'),
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
