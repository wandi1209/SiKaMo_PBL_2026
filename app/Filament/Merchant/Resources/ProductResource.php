<?php

namespace App\Filament\Merchant\Resources;

use App\Filament\Merchant\Resources\ProductResource\Pages;
use App\Models\Category;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Get;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $modelLabel = 'Produk';
    protected static ?string $pluralModelLabel = 'Produk';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Hidden::make('existing_product_id')
                ->default(fn (string $operation): ?int => $operation === 'create'
                    ? static::findExistingProductIdByBarcode(request()->query('barcode'))
                    : null)
                ->dehydrated(fn (string $operation): bool => $operation === 'create'),

            Forms\Components\TextInput::make('barcode')
                ->label(fn (string $operation): string => $operation === 'create' ? 'Scan / Ketik Barcode' : 'Barcode')
                ->helperText(fn (string $operation): ?string => $operation === 'create'
                    ? 'Scan barcode terlebih dahulu. Jika produk sudah ada, sistem akan menawarkan tambah stok.'
                    : null)
                ->suffixIcon('heroicon-o-qr-code')
                ->autofocus()
                ->default(fn (string $operation): ?string => $operation === 'create' ? request()->query('barcode') : null)
                ->afterStateHydrated(function (?string $state, Set $set, string $operation): void {
                    if ($operation !== 'create') {
                        return;
                    }

                    $barcode = trim((string) $state);

                    if ($barcode === '') {
                        return;
                    }

                    $set('existing_product_id', static::findExistingProductIdByBarcode($barcode));
                })
                ->live(debounce: 500)
                ->afterStateUpdated(function (?string $state, Set $set, string $operation): void {
                    if ($operation !== 'create') {
                        return;
                    }

                    $barcode = trim((string) $state);

                    if ($barcode === '') {
                        $set('existing_product_id', null);

                        return;
                    }

                    $existingProduct = static::findExistingProductByBarcode($barcode);

                    $set('existing_product_id', $existingProduct?->id);

                    if ($existingProduct) {
                        Notification::make()
                            ->title('Barcode sudah terdaftar')
                            ->body("Produk {$existingProduct->name} ditemukan. Silakan tambah jumlah stok.")
                            ->warning()
                            ->send();
                    }
                })
                ->rules([
                    fn (Get $get, ?Product $record, string $operation): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record, $operation): void {
                        if (blank($value) || ($operation === 'create' && filled($get('existing_product_id')))) {
                            return;
                        }

                        $barcodeExists = Product::query()
                            ->where('merchant_id', filament()->getTenant()?->id)
                            ->where('barcode', $value)
                            ->when($record, fn (Builder $query) => $query->whereKeyNot($record->getKey()))
                            ->exists();

                        if ($barcodeExists) {
                            $fail('Barcode ini sudah digunakan oleh produk lain.');
                        }
                    },
                ])
                ->maxLength(255)
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),

            Forms\Components\Placeholder::make('barcode_not_found')
                ->label('Status Barcode')
                ->content('Barcode belum terdaftar. Silakan lengkapi data produk baru.')
                ->visible(fn (Get $get, string $operation): bool => $operation === 'create'
                    && filled($get('barcode'))
                    && ! static::isExistingProductMode($get, $operation)),

            Forms\Components\Section::make('Produk sudah ada')
                ->description('Masukkan jumlah stok yang ingin ditambahkan ke produk ini.')
                ->schema([
                    Forms\Components\Placeholder::make('existing_product_name')
                        ->label('Produk')
                        ->content(function (Get $get): string {
                            $product = Product::query()
                                ->where('merchant_id', filament()->getTenant()?->id)
                                ->find($get('existing_product_id'));

                            return $product
                                ? "{$product->name} - stok saat ini: " . number_format($product->stock, 0, ',', '.')
                                : '-';
                        }),

                    Forms\Components\TextInput::make('stock_increment')
                        ->label('Tambah Stok')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->required(fn (Get $get, string $operation): bool => $operation === 'create' && filled($get('existing_product_id')))
                        ->dehydrated(fn (string $operation): bool => $operation === 'create'),
                ])
                ->visible(fn (Get $get, string $operation): bool => static::isExistingProductMode($get, $operation))
                ->columns(2),

            Forms\Components\Select::make('category_id')
                ->label('Kategori')
                ->relationship(
                    name: 'category',
                    titleAttribute: 'name',
                    // Hanya tampilkan kategori milik merchant ini
                    modifyQueryUsing: fn (Builder $query) => $query->where(
                        'merchant_id', filament()->getTenant()?->id
                    )
                )
                ->required(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation))
                ->searchable()
                ->preload()
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),

            Forms\Components\TextInput::make('name')
                ->label('Nama Produk')
                ->required(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation))
                ->maxLength(255)
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),

            Forms\Components\TextInput::make('price')
                ->label('Harga')
                ->numeric()
                ->prefix('Rp')
                ->required(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation))
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),

            Forms\Components\TextInput::make('stock')
                ->label('Stok')
                ->numeric()
                ->required(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation))
                ->default(0)
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),

            Forms\Components\TextInput::make('low_stock_threshold')
                ->label('Batas Stok Rendah')
                ->numeric()
                ->required(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation))
                ->default(5)
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),

            Forms\Components\FileUpload::make('image')
                ->label('Foto Produk')
                ->image()
                ->directory('products')
                ->nullable()
                ->visible(fn (Get $get, string $operation): bool => ! static::isExistingProductMode($get, $operation)),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Foto')
                    ->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('barcode')
                    ->label('Barcode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('stock')
                    ->label('Stok')
                    ->sortable()
                    ->color(fn ($record) => $record->stock <= $record->low_stock_threshold ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'name'),
            ])
            ->actions([
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
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    private static function findExistingProductIdByBarcode(mixed $barcode): ?int
    {
        return static::findExistingProductByBarcode($barcode)?->id;
    }

    private static function findExistingProductByBarcode(mixed $barcode): ?Product
    {
        $barcode = trim((string) $barcode);

        if ($barcode === '') {
            return null;
        }

        return Product::query()
            ->where('merchant_id', filament()->getTenant()?->id)
            ->where('barcode', $barcode)
            ->first();
    }

    private static function isExistingProductMode(Get $get, string $operation): bool
    {
        if ($operation !== 'create') {
            return false;
        }

        if (filled($get('existing_product_id'))) {
            return true;
        }

        return static::findExistingProductIdByBarcode($get('barcode')) !== null;
    }
}
