<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MerchantResource\Pages;
use App\Filament\Resources\MerchantResource\RelationManagers;
use App\Models\Merchant;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MerchantResource extends Resource
{
    protected static ?string $model = Merchant::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel = 'Merchant';
    protected static ?string $modelLabel = 'Merchant';
    protected static ?string $pluralModelLabel = 'Merchants';
    protected static ?string $navigationGroup = 'Manajemen';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Toko')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Toko')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                        $set('slug', \Illuminate\Support\Str::slug($state))
                    ),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug (untuk URL/subdomain)')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),

                Forms\Components\Select::make('owner_id')
                    ->label('Owner')
                    ->options(\App\Models\User::where('role', 'owner')->pluck('name', 'id'))
                    ->searchable()
                    ->preload()
                    ->nullable(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),
            ])->columns(2),

            Forms\Components\Section::make('Kontak')->schema([
                Forms\Components\TextInput::make('phone')
                    ->label('No. Telepon')
                    ->tel()
                    ->nullable(),

                Forms\Components\Textarea::make('address')
                    ->label('Alamat')
                    ->nullable()
                    ->rows(3),

                Forms\Components\FileUpload::make('logo')
                    ->label('Logo')
                    ->image()
                    ->directory('merchants/logos')
                    ->nullable(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('logo')
                    ->label('Logo')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Toko')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('owner.name')
                    ->label('Owner')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telepon'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                Tables\Columns\TextColumn::make('monthly_revenue')
                    ->label('Omzet Bulan Ini')
                    ->state(fn (Merchant $record): float => static::monthlyRevenue($record))
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('monthly_transactions')
                    ->label('Transaksi Bulan Ini')
                    ->state(fn (Merchant $record): int => static::monthlyTransactions($record))
                    ->badge(),

                Tables\Columns\TextColumn::make('low_stock_products')
                    ->label('Stok Menipis')
                    ->state(fn (Merchant $record): int => $record->products()->whereColumn('stock', '<=', 'low_stock_threshold')->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Daftar')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status'),
            ])
            ->headerActions([
                Tables\Actions\Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn (): StreamedResponse => static::exportCsv()),
            ])
            ->actions([
                Tables\Actions\Action::make('impersonate')
                    ->label('Login sbg Owner')
                    ->icon('heroicon-o-eye')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Login sebagai Owner')
                    ->modalDescription('Anda akan login sebagai owner merchant ini. Jika ingin kembali ke panel Admin, Anda cukup tekan Logout di panel merchant nanti.')
                    ->action(function ($record) {
                        $owner = $record->owner;
                        if ($owner) {
                            // Simpan ke session jika diperlukan fitur "leave impersonation" di kemudian hari
                            session()->put('impersonate_original_id', auth()->id());
                            
                            auth()->login($owner);
                            session()->put('password_hash_web', $owner->getAuthPassword());
                            
                            return redirect('/merchant');
                        }
                    })
                    ->visible(fn ($record) => $record->owner !== null),
                    
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMerchants::route('/'),
            'create' => Pages\CreateMerchant::route('/create'),
            'edit'   => Pages\EditMerchant::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProductsRelationManager::class,
            RelationManagers\CashiersRelationManager::class,
        ];
    }

    private static function monthlyRevenue(Merchant $merchant): float
    {
        return (float) DB::table('t_transactions')
            ->select('t_transactions.id', 't_transactions.total_amount')
            ->join('t_transaction_items', 't_transaction_items.transaction_id', '=', 't_transactions.id')
            ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
            ->where('m_products.merchant_id', $merchant->id)
            ->where('t_transactions.status', 'completed')
            ->whereBetween('t_transactions.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('t_transactions.id', 't_transactions.total_amount')
            ->get()
            ->sum('total_amount');
    }

    private static function monthlyTransactions(Merchant $merchant): int
    {
        return DB::table('t_transactions')
            ->join('t_transaction_items', 't_transaction_items.transaction_id', '=', 't_transactions.id')
            ->join('m_products', 'm_products.id', '=', 't_transaction_items.product_id')
            ->where('m_products.merchant_id', $merchant->id)
            ->where('t_transactions.status', 'completed')
            ->whereBetween('t_transactions.created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->distinct('t_transactions.id')
            ->count('t_transactions.id');
    }

    private static function exportCsv(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Nama Toko', 'Owner', 'Telepon', 'Status', 'Omzet Bulan Ini', 'Transaksi Bulan Ini']);

            Merchant::query()
                ->with('owner')
                ->orderBy('name')
                ->chunk(100, function ($merchants) use ($handle): void {
                    foreach ($merchants as $merchant) {
                        fputcsv($handle, [
                            $merchant->name,
                            $merchant->owner?->name,
                            $merchant->phone,
                            $merchant->is_active ? 'Aktif' : 'Nonaktif',
                            static::monthlyRevenue($merchant),
                            static::monthlyTransactions($merchant),
                        ]);
                    }
                });

            fclose($handle);
        }, 'merchants.csv');
    }
}
