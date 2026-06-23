<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-receipt-percent';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $modelLabel = 'Transaksi';
    protected static ?string $pluralModelLabel = 'Riwayat Transaksi';
    protected static ?int $navigationSort = 1;
    protected static bool $shouldRegisterNavigation = false;

    // Tenant isolation (UU PDP): the platform admin must NOT browse individual
    // tenant transactions / omzet. Disabled entirely — admin only sees
    // platform-level aggregate metrics on the dashboard.
    public static function canViewAny(): bool
    {
        return false;
    }

    public static function canView(\Illuminate\Database\Eloquent\Model $record): bool
    {
        return false;
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Transaksi')
                ->schema([
                    Forms\Components\TextInput::make('invoice_number')->label('Invoice')->disabled(),
                    Forms\Components\TextInput::make('user.name')->label('Kasir')->disabled(),
                    Forms\Components\TextInput::make('total_amount')->label('Total')->prefix('Rp')->disabled(),
                    Forms\Components\TextInput::make('payment_method')->label('Metode Pembayaran')->disabled(),
                    Forms\Components\TextInput::make('status')->label('Status')->disabled(),
                    Forms\Components\TextInput::make('created_at')->label('Waktu')->disabled(),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->with(['user', 'items.product.merchant']))
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label('Invoice')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('merchant_name')
                    ->label('Merchant')
                    ->state(fn(Transaction $record): string => $record->merchant?->name ?? '-')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('items.product.merchant', fn(Builder $merchantQuery) => $merchantQuery->where('name', 'like', "%{$search}%"));
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Kasir')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Pembayaran')
                    ->badge()
                    ->formatStateUsing(fn(?string $state): string => strtoupper((string) $state)),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'completed' => 'success',
                        'pending' => 'warning',
                        'cancelled' => 'danger',
                        'refunded' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
                Tables\Filters\SelectFilter::make('payment_method')
                    ->options([
                        'cash' => 'Cash',
                        'qris' => 'QRIS',
                        'debit' => 'Debit',
                        'credit' => 'Credit',
                        'transfer' => 'Transfer',
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
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Invoice', 'Merchant', 'Kasir', 'Total', 'Pembayaran', 'Status', 'Waktu']);

            Transaction::query()
                ->with(['user', 'items.product.merchant'])
                ->latest()
                ->chunk(200, function ($transactions) use ($handle): void {
                    foreach ($transactions as $transaction) {
                        fputcsv($handle, [
                            $transaction->invoice_number,
                            $transaction->merchant?->name,
                            $transaction->user?->name,
                            $transaction->total_amount,
                            $transaction->payment_method,
                            $transaction->status,
                            $transaction->created_at?->format('Y-m-d H:i:s'),
                        ]);
                    }
                });

            fclose($handle);
        }, 'riwayat-transaksi.csv');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransactions::route('/'),
            'view' => Pages\ViewTransaction::route('/{record}'),
        ];
    }
}
