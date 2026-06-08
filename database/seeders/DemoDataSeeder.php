<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DailyReport;
use App\Models\Discount;
use App\Models\Member;
use App\Models\Merchant;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockLog;
use App\Models\Supplier;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * @var array<int, array{name: string, category: string, supplier: string, cost: int, price: int, opening: int, low: int}>
     */
    private array $productTemplates = [
        ['name' => 'Beras Premium 5kg', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 64000, 'price' => 75000, 'opening' => 70, 'low' => 10],
        ['name' => 'Minyak Goreng 2L', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 31000, 'price' => 36500, 'opening' => 82, 'low' => 12],
        ['name' => 'Gula Pasir 1kg', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 14500, 'price' => 17500, 'opening' => 96, 'low' => 15],
        ['name' => 'Telur Ayam 1kg', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 24500, 'price' => 29000, 'opening' => 64, 'low' => 10],
        ['name' => 'Indomie Goreng', 'category' => 'Makanan Ringan', 'supplier' => 'UD Camilan Jaya', 'cost' => 2800, 'price' => 3500, 'opening' => 180, 'low' => 25],
        ['name' => 'Keripik Singkong', 'category' => 'Makanan Ringan', 'supplier' => 'UD Camilan Jaya', 'cost' => 11000, 'price' => 15000, 'opening' => 75, 'low' => 12],
        ['name' => 'Oreo Vanilla 133g', 'category' => 'Makanan Ringan', 'supplier' => 'UD Camilan Jaya', 'cost' => 8200, 'price' => 10500, 'opening' => 88, 'low' => 15],
        ['name' => 'Chitato Sapi Panggang', 'category' => 'Makanan Ringan', 'supplier' => 'UD Camilan Jaya', 'cost' => 9200, 'price' => 12500, 'opening' => 80, 'low' => 14],
        ['name' => 'Aqua Galon 19L', 'category' => 'Minuman', 'supplier' => 'PT Segar Sentosa', 'cost' => 18000, 'price' => 22000, 'opening' => 48, 'low' => 8],
        ['name' => 'Teh Pucuk 350ml', 'category' => 'Minuman', 'supplier' => 'PT Segar Sentosa', 'cost' => 3100, 'price' => 4500, 'opening' => 140, 'low' => 20],
        ['name' => 'Kopi Kapal Api Mix', 'category' => 'Minuman', 'supplier' => 'PT Segar Sentosa', 'cost' => 11200, 'price' => 14500, 'opening' => 115, 'low' => 18],
        ['name' => 'Susu UHT Coklat', 'category' => 'Minuman', 'supplier' => 'PT Segar Sentosa', 'cost' => 5100, 'price' => 7000, 'opening' => 96, 'low' => 15],
        ['name' => 'Sabun Lifebuoy', 'category' => 'Kebersihan', 'supplier' => 'PT Bersih Sejahtera', 'cost' => 3800, 'price' => 5000, 'opening' => 105, 'low' => 18],
        ['name' => 'Rinso Bubuk 800g', 'category' => 'Kebersihan', 'supplier' => 'PT Bersih Sejahtera', 'cost' => 27000, 'price' => 32000, 'opening' => 60, 'low' => 10],
        ['name' => 'Pasta Gigi Pepsodent', 'category' => 'Kebersihan', 'supplier' => 'PT Bersih Sejahtera', 'cost' => 8700, 'price' => 11500, 'opening' => 90, 'low' => 15],
        ['name' => 'Kecap Bango 550ml', 'category' => 'Bumbu Dapur', 'supplier' => 'CV Sumber Pangan', 'cost' => 20000, 'price' => 24000, 'opening' => 70, 'low' => 10],
        ['name' => 'Saus Sambal ABC', 'category' => 'Bumbu Dapur', 'supplier' => 'CV Sumber Pangan', 'cost' => 9800, 'price' => 13000, 'opening' => 84, 'low' => 12],
        ['name' => 'Garam Dapur', 'category' => 'Bumbu Dapur', 'supplier' => 'CV Sumber Pangan', 'cost' => 2100, 'price' => 3000, 'opening' => 130, 'low' => 20],
    ];

    /**
     * @var array<int, array{slug: string, name: string, owner: string, city: string, phone: string, address: string}>
     */
    private array $merchantTemplates = [
        ['slug' => 'toko-budi', 'name' => 'Toko Budi', 'owner' => 'Budi Santoso', 'city' => 'Malang', 'phone' => '08123456789', 'address' => 'Jl. Contoh No. 1, Malang'],
        ['slug' => 'warung-sari-rasa', 'name' => 'Warung Sari Rasa', 'owner' => 'Sari Wulandari', 'city' => 'Surabaya', 'phone' => '08123456790', 'address' => 'Jl. Manyar No. 22, Surabaya'],
        ['slug' => 'minimarket-makmur', 'name' => 'Minimarket Makmur', 'owner' => 'Hendra Wijaya', 'city' => 'Sidoarjo', 'phone' => '08123456791', 'address' => 'Jl. Pahlawan No. 18, Sidoarjo'],
        ['slug' => 'toko-amanah', 'name' => 'Toko Amanah', 'owner' => 'Nur Aisyah', 'city' => 'Kediri', 'phone' => '08123456792', 'address' => 'Jl. Diponegoro No. 9, Kediri'],
        ['slug' => 'kedai-sejahtera', 'name' => 'Kedai Sejahtera', 'owner' => 'Agus Prasetyo', 'city' => 'Blitar', 'phone' => '08123456793', 'address' => 'Jl. Merdeka No. 14, Blitar'],
        ['slug' => 'toserba-nusantara', 'name' => 'Toserba Nusantara', 'owner' => 'Rina Amelia', 'city' => 'Madiun', 'phone' => '08123456794', 'address' => 'Jl. Panglima Sudirman No. 31, Madiun'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            User::updateOrCreate(
                ['email' => 'admin@sikamo.com'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('admin123'),
                    'is_active' => true,
                    'role' => 'admin',
                    'merchant_id' => null,
                ],
            );

            foreach ($this->merchantTemplates as $index => $template) {
                $this->seedMerchant($template, $index + 1);
            }
        });

        $this->command?->info('DemoDataSeeder selesai.');
        $this->command?->info('Admin: admin@sikamo.com / admin123');
        $this->command?->info('Owner utama: owner@tokobudi.com / owner123');
        $this->command?->info('Kasir utama: kasir@tokobudi.com / kasir123');
    }

    /**
     * @param array{slug: string, name: string, owner: string, city: string, phone: string, address: string} $template
     */
    private function seedMerchant(array $template, int $merchantNumber): void
    {
        $ownerEmail = $merchantNumber === 1
            ? 'owner@tokobudi.com'
            : 'owner' . $merchantNumber . '@sikamo.test';

        $owner = User::updateOrCreate(
            ['email' => $ownerEmail],
            [
                'name' => $template['owner'],
                'password' => Hash::make($merchantNumber === 1 ? 'owner123' : 'owner12345'),
                'is_active' => true,
                'role' => 'owner',
                'merchant_id' => null,
            ],
        );

        $merchant = Merchant::updateOrCreate(
            ['slug' => $template['slug']],
            [
                'owner_id' => $owner->id,
                'name' => $template['name'],
                'phone' => $template['phone'],
                'address' => $template['address'],
                'operational_start_time' => '07:00',
                'operational_end_time' => $merchantNumber % 2 === 0 ? '21:30' : '22:00',
                'qris_payload' => $this->demoQrisPayload($template['name'], $template['city']),
                'member_discount_is_active' => true,
                'member_discount_type' => 'percent',
                'member_discount_value' => 5 + ($merchantNumber % 3) * 2,
                'member_discount_max_amount' => 15000 + ($merchantNumber * 2500),
                'member_discount_min_purchase' => 35000 + ($merchantNumber * 5000),
                'is_active' => true,
            ],
        );

        $cashiers = $this->seedCashiers($merchant, $merchantNumber);
        $categories = $this->seedCategories($merchant);
        $suppliers = $this->seedSuppliers($merchant, $template['city'], $merchantNumber);
        $products = $this->seedProducts($merchant, $categories, $suppliers, $merchantNumber);
        $members = $this->seedMembers($merchant, $merchantNumber);
        $discounts = $this->seedDiscounts($merchant, $merchantNumber);

        $this->seedOpeningStockLogs($products, $owner, $merchantNumber);
        $this->seedPurchases($merchant, $owner, $suppliers, $products, $merchantNumber);
        $transactions = $this->seedTransactions($merchant, $cashiers, $members, $discounts, $products, $merchantNumber);
        $this->seedDailyReports($merchant, $transactions);
    }

    /**
     * @return Collection<int, User>
     */
    private function seedCashiers(Merchant $merchant, int $merchantNumber): Collection
    {
        $rows = [
            [
                'email' => $merchantNumber === 1 ? 'kasir@tokobudi.com' : 'kasir' . $merchantNumber . 'a@sikamo.test',
                'name' => $merchantNumber === 1 ? 'Siti Kasir' : 'Kasir ' . $merchantNumber . ' A',
            ],
            [
                'email' => $merchantNumber === 1 ? 'kasir2@tokobudi.com' : 'kasir' . $merchantNumber . 'b@sikamo.test',
                'name' => $merchantNumber === 1 ? 'Andi Kasir' : 'Kasir ' . $merchantNumber . ' B',
            ],
        ];

        return collect($rows)->map(fn (array $row): User => User::updateOrCreate(
            ['email' => $row['email']],
            [
                'name' => $row['name'],
                'password' => Hash::make($merchantNumber === 1 ? 'kasir123' : 'kasir12345'),
                'is_active' => true,
                'role' => 'cashier',
                'merchant_id' => $merchant->id,
            ],
        ));
    }

    /**
     * @return Collection<string, Category>
     */
    private function seedCategories(Merchant $merchant): Collection
    {
        return collect([
            'Sembako' => 'Bahan pokok kebutuhan dapur.',
            'Makanan Ringan' => 'Camilan dan makanan kemasan.',
            'Minuman' => 'Minuman siap saji dan produk seduh.',
            'Kebersihan' => 'Produk kebersihan rumah tangga.',
            'Bumbu Dapur' => 'Bumbu masak dan pelengkap dapur.',
        ])->map(fn (string $description, string $name): Category => Category::updateOrCreate(
            ['merchant_id' => $merchant->id, 'name' => $name],
            ['description' => $description],
        ));
    }

    /**
     * @return Collection<string, Supplier>
     */
    private function seedSuppliers(Merchant $merchant, string $city, int $merchantNumber): Collection
    {
        $rows = [
            ['name' => 'CV Sumber Pangan', 'suffix' => 'pangan'],
            ['name' => 'UD Camilan Jaya', 'suffix' => 'camilan'],
            ['name' => 'PT Segar Sentosa', 'suffix' => 'segar'],
            ['name' => 'PT Bersih Sejahtera', 'suffix' => 'bersih'],
        ];

        return collect($rows)->mapWithKeys(fn (array $row, int $index): array => [
            $row['name'] => Supplier::updateOrCreate(
                ['merchant_id' => $merchant->id, 'name' => $row['name']],
                [
                    'phone' => '0811' . str_pad((string) ($merchantNumber * 1000 + $index + 1), 8, '0', STR_PAD_LEFT),
                    'email' => $row['suffix'] . $merchantNumber . '@supplier.test',
                    'address' => 'Gudang ' . $city . ' No. ' . ($index + 1),
                    'notes' => 'Supplier demo untuk ' . $merchant->name . '.',
                    'is_active' => true,
                ],
            ),
        ]);
    }

    /**
     * @param Collection<string, Category> $categories
     * @param Collection<string, Supplier> $suppliers
     * @return Collection<int, array{model: Product, opening: int}>
     */
    private function seedProducts(Merchant $merchant, Collection $categories, Collection $suppliers, int $merchantNumber): Collection
    {
        return collect($this->productTemplates)->map(function (array $template, int $index) use ($merchant, $categories, $suppliers, $merchantNumber): array {
            $priceFactor = 1 + (($merchantNumber - 1) * 0.025);
            $opening = $template['opening'] + ($merchantNumber * 4) + ($index % 5);
            $barcode = '899' . str_pad((string) $merchantNumber, 2, '0', STR_PAD_LEFT) . str_pad((string) ($index + 1), 9, '0', STR_PAD_LEFT);

            $product = Product::updateOrCreate(
                ['merchant_id' => $merchant->id, 'barcode' => $barcode],
                [
                    'supplier_id' => $suppliers[$template['supplier']]->id,
                    'category_id' => $categories[$template['category']]->id,
                    'name' => $template['name'],
                    'price' => (int) round($template['price'] * $priceFactor / 500) * 500,
                    'cost_price' => (int) round($template['cost'] * $priceFactor / 100) * 100,
                    'stock' => $opening,
                    'low_stock_threshold' => $template['low'],
                ],
            );

            return ['model' => $product, 'opening' => $opening];
        });
    }

    /**
     * @return Collection<int, Member>
     */
    private function seedMembers(Merchant $merchant, int $merchantNumber): Collection
    {
        $names = ['Rina Amelia', 'Dimas Pratama', 'Maya Lestari', 'Fajar Nugroho', 'Laila Putri', 'Reza Firmansyah'];

        return collect($names)->map(function (string $name, int $index) use ($merchant, $merchantNumber): Member {
            $phone = '0822' . str_pad((string) ($merchantNumber * 100000 + $index + 1), 8, '0', STR_PAD_LEFT);

            return Member::updateOrCreate(
                ['merchant_id' => $merchant->id, 'phone' => $phone],
                [
                    'name' => $name,
                    'email' => Str::slug($name) . $merchantNumber . '@member.test',
                    'address' => 'Alamat member demo ' . ($index + 1),
                    'joined_at' => now()->subMonths(3)->addDays($index)->toDateString(),
                    'notes' => 'Member demo SiKaMo.',
                    'is_active' => true,
                ],
            );
        });
    }

    /**
     * @return Collection<int, Discount>
     */
    private function seedDiscounts(Merchant $merchant, int $merchantNumber): Collection
    {
        return collect([
            Discount::updateOrCreate(
                ['merchant_id' => $merchant->id, 'code' => 'HEMAT' . (10 + $merchantNumber)],
                [
                    'name' => 'Diskon Hemat ' . (10 + $merchantNumber) . '%',
                    'type' => 'percent',
                    'value' => 10 + $merchantNumber,
                    'max_discount_amount' => 25000,
                    'min_purchase_amount' => 50000,
                    'quota' => 250,
                    'used_count' => 0,
                    'starts_at' => now()->subMonths(2),
                    'ends_at' => now()->addMonths(2),
                    'is_active' => true,
                ],
            ),
            Discount::updateOrCreate(
                ['merchant_id' => $merchant->id, 'code' => 'POTONG' . (5000 + ($merchantNumber * 1000))],
                [
                    'name' => 'Potongan Langsung',
                    'type' => 'fixed',
                    'value' => 5000 + ($merchantNumber * 1000),
                    'max_discount_amount' => null,
                    'min_purchase_amount' => 35000,
                    'quota' => null,
                    'used_count' => 0,
                    'starts_at' => now()->subMonths(2),
                    'ends_at' => now()->addMonths(2),
                    'is_active' => true,
                ],
            ),
        ]);
    }

    /**
     * @param Collection<int, array{model: Product, opening: int}> $products
     */
    private function seedOpeningStockLogs(Collection $products, User $owner, int $merchantNumber): void
    {
        foreach ($products as $index => $entry) {
            StockLog::create([
                'product_id' => $entry['model']->id,
                'user_id' => $owner->id,
                'quantity_change' => $entry['opening'],
                'stock_before' => 0,
                'stock_after' => $entry['opening'],
                'type' => 'in',
                'note' => 'Seeder demo: stok awal produk',
                'created_at' => now()->subMonths(3)->addDays($merchantNumber + $index),
                'updated_at' => now()->subMonths(3)->addDays($merchantNumber + $index),
            ]);
        }
    }

    /**
     * @param Collection<string, Supplier> $suppliers
     * @param Collection<int, array{model: Product, opening: int}> $products
     */
    private function seedPurchases(Merchant $merchant, User $owner, Collection $suppliers, Collection $products, int $merchantNumber): void
    {
        foreach ([1, 2, 3] as $purchaseIndex) {
            $supplier = $suppliers->values()[($purchaseIndex + $merchantNumber) % $suppliers->count()];
            $date = now()->subMonths(2)->addWeeks($purchaseIndex + $merchantNumber);
            $invoice = 'PO-' . str_pad((string) $merchantNumber, 2, '0', STR_PAD_LEFT) . '-' . $date->format('Ymd') . '-' . $purchaseIndex;

            $purchase = Purchase::create([
                'merchant_id' => $merchant->id,
                'supplier_id' => $supplier->id,
                'user_id' => $owner->id,
                'invoice_number' => $invoice,
                'purchase_date' => $date->toDateString(),
                'total_amount' => 0,
                'status' => 'completed',
                'notes' => 'Seeder demo: restock supplier.',
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $total = 0;

            foreach ($products->slice(($purchaseIndex - 1) * 5, 6) as $entry) {
                $product = $entry['model'];
                $quantity = 12 + ($purchaseIndex * 3) + ($merchantNumber % 4);
                $subtotal = ((float) $product->cost_price) * $quantity;
                $total += $subtotal;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_cost' => $product->cost_price,
                    'subtotal' => $subtotal,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);

                $before = $product->stock;
                $product->increment('stock', $quantity);

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id' => $owner->id,
                    'quantity_change' => $quantity,
                    'stock_before' => $before,
                    'stock_after' => $before + $quantity,
                    'type' => 'in',
                    'note' => 'Seeder demo: pembelian ' . $invoice,
                    'created_at' => $date,
                    'updated_at' => $date,
                ]);
            }

            $purchase->forceFill(['total_amount' => $total])->save();
        }
    }

    /**
     * @param Collection<int, User> $cashiers
     * @param Collection<int, Member> $members
     * @param Collection<int, Discount> $discounts
     * @param Collection<int, array{model: Product, opening: int}> $products
     * @return Collection<int, Transaction>
     */
    private function seedTransactions(Merchant $merchant, Collection $cashiers, Collection $members, Collection $discounts, Collection $products, int $merchantNumber): Collection
    {
        $transactions = collect();
        $baseDate = CarbonImmutable::now()->startOfMonth()->subMonths(2);

        for ($i = 1; $i <= 45; $i++) {
            $date = $baseDate
                ->addDays(($i * 2 + $merchantNumber) % 76)
                ->setTime(8 + ($i % 12), ($i * 7) % 60);

            $invoiceNumber = 'INV-' . str_pad((string) $merchantNumber, 2, '0', STR_PAD_LEFT) . '-' . $date->format('Ymd') . '-' . str_pad((string) $i, 4, '0', STR_PAD_LEFT);
            $cashier = $cashiers[$i % $cashiers->count()];
            $member = $i % 3 === 0 ? $members[$i % $members->count()] : null;
            $discount = $i % 5 === 0 ? $discounts[$i % $discounts->count()] : null;
            $paymentMethod = $i % 4 === 0 ? 'qris' : 'cash';

            $lineCount = 2 + ($i % 4);
            $items = collect(range(0, $lineCount - 1))->map(function (int $offset) use ($products, $i): array {
                $entry = $products[($i + ($offset * 3)) % $products->count()];

                return [
                    'product' => $entry['model'],
                    'quantity' => 1 + (($i + $offset) % 4),
                ];
            });

            $transactions->push($this->createTransaction($invoiceNumber, $cashier, $member, $discount, $paymentMethod, $items, $date));
        }

        return $transactions;
    }

    /**
     * @param Collection<int, array{product: Product, quantity: int}> $items
     */
    private function createTransaction(string $invoiceNumber, User $cashier, ?Member $member, ?Discount $discount, string $paymentMethod, Collection $items, CarbonImmutable $date): Transaction
    {
        $subtotal = 0;

        foreach ($items as $item) {
            $subtotal += ((float) $item['product']->price) * $item['quantity'];
        }

        $voucherDiscount = ($discount && $discount->isUsableFor($subtotal)) ? $discount->calculateDiscount($subtotal) : 0;
        $memberDiscount = $member ? min(($subtotal - $voucherDiscount) * 0.05, 20000) : 0;
        $totalDiscount = $voucherDiscount + $memberDiscount;
        $total = max(0, $subtotal - $totalDiscount);
        $paid = $paymentMethod === 'cash' ? ceil($total / 10000) * 10000 : $total;

        $transaction = Transaction::create([
            'user_id' => $cashier->id,
            'discount_id' => $discount?->id,
            'discount_code' => $discount?->code,
            'member_id' => $member?->id,
            'member_name' => $member?->name,
            'member_phone' => $member?->phone,
            'invoice_number' => $invoiceNumber,
            'subtotal_amount' => $subtotal,
            'discount_amount' => $totalDiscount,
            'member_discount_amount' => $memberDiscount,
            'total_amount' => $total,
            'amount_paid' => $paid,
            'change_amount' => $paid - $total,
            'payment_method' => $paymentMethod,
            'status' => 'completed',
            'created_at' => $date,
            'updated_at' => $date,
        ]);

        $allocatedDiscount = 0;
        $lastIndex = $items->count() - 1;

        foreach ($items->values() as $index => $item) {
            /** @var Product $product */
            $product = $item['product']->fresh();
            $quantity = $item['quantity'];
            $itemSubtotal = ((float) $product->price) * $quantity;
            $itemCost = ((float) $product->cost_price) * $quantity;
            $itemDiscount = $index === $lastIndex
                ? $totalDiscount - $allocatedDiscount
                : round($totalDiscount * ($itemSubtotal / max($subtotal, 1)), 2);
            $allocatedDiscount += $itemDiscount;

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'unit_price' => $product->price,
                'unit_cost' => $product->cost_price,
                'subtotal' => $itemSubtotal,
                'total_cost' => $itemCost,
                'gross_profit' => $itemSubtotal - $itemDiscount - $itemCost,
                'created_at' => $date,
                'updated_at' => $date,
            ]);

            $before = $product->stock;
            $after = max(0, $before - $quantity);
            $product->forceFill(['stock' => $after])->save();

            StockLog::create([
                'product_id' => $product->id,
                'user_id' => $cashier->id,
                'quantity_change' => -1 * $quantity,
                'stock_before' => $before,
                'stock_after' => $after,
                'type' => 'out',
                'note' => 'Seeder demo: penjualan ' . $invoiceNumber,
                'created_at' => $date,
                'updated_at' => $date,
            ]);
        }

        if ($discount) {
            $discount->increment('used_count');
        }

        return $transaction;
    }

    /**
     * @param Collection<int, Transaction> $transactions
     */
    private function seedDailyReports(Merchant $merchant, Collection $transactions): void
    {
        $transactions
            ->groupBy(fn (Transaction $transaction): string => $transaction->created_at->toDateString())
            ->each(function (Collection $dailyTransactions, string $date) use ($merchant): void {
                DailyReport::updateOrCreate(
                    ['merchant_id' => $merchant->id, 'report_date' => $date],
                    [
                        'total_transactions' => $dailyTransactions->count(),
                        'total_revenue' => $dailyTransactions->sum('total_amount'),
                        'total_items_sold' => TransactionItem::whereIn('transaction_id', $dailyTransactions->pluck('id'))->sum('quantity'),
                        'sent_to_whatsapp' => false,
                        'sent_at' => null,
                    ],
                );
            });
    }

    private function demoQrisPayload(string $merchantName, string $city): string
    {
        $name = strtoupper(Str::limit($merchantName, 18, ''));
        $cityName = strtoupper(Str::limit($city, 12, ''));

        return '00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214531234567890180303UMI51440014ID.CO.QRIS.WWW0215ID10200255943330303UMI5204541153033605802ID59'
            . str_pad((string) strlen($name), 2, '0', STR_PAD_LEFT) . $name
            . '60' . str_pad((string) strlen($cityName), 2, '0', STR_PAD_LEFT) . $cityName
            . '61056514162070703A01';
    }
}
