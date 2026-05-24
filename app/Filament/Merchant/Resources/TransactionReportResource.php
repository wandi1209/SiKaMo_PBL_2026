<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\TransactionReportResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
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
            ->with(['user', 'refundedBy', 'items.product.category'])
            ->whereIn('status', ['completed', 'refunded'])
            ->whereHas('items.product', fn(Builder $query) => $query->where('merchant_id', $merchantId));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Detail Transaksi')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')->label('Invoice')->disabled(),
                    Forms\Components\TextInput::make('user.name')->label('Kasir')->disabled(),
                    Forms\Components\TextInput::make('payment_method')->label('Metode Pembayaran')->disabled(),
                    Forms\Components\TextInput::make('total_amount')->label('Total')->prefix('Rp')->disabled(),
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
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('start_date')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('end_date')->label('Sampai Tanggal'),
                    ])
                    ->query(fn(Builder $query, array $data): Builder => $query
                        ->when($data['start_date'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date))
                        ->when($data['end_date'] ?? null, fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date))),

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

            fputcsv($handle, ['Invoice', 'Waktu', 'Kasir', 'Metode Pembayaran', 'Total', 'Item']);

            static::getEloquentQuery()
                ->whereHas('items.product', fn(Builder $query) => $query->where('merchant_id', $merchantId))
                ->latest()
                ->chunk(200, function ($transactions) use ($handle): void {
                    foreach ($transactions as $transaction) {
                        fputcsv($handle, [
                            $transaction->invoice_number,
                            $transaction->created_at?->format('Y-m-d H:i:s'),
                            $transaction->user?->name,
                            $transaction->payment_method,
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
}
