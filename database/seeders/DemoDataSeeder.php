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
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $admin = User::updateOrCreate(
                ['email' => 'admin@sikamo.com'],
                [
                    'name' => 'Administrator',
                    'password' => Hash::make('admin123'),
                    'is_active' => true,
                    'role' => 'admin',
                    'merchant_id' => null,
                ],
            );

            $owner = User::updateOrCreate(
                ['email' => 'owner@tokobudi.com'],
                [
                    'name' => 'Budi Santoso',
                    'password' => Hash::make('owner123'),
                    'is_active' => true,
                    'role' => 'owner',
                    'merchant_id' => null,
                ],
            );

            $merchant = Merchant::updateOrCreate(
                ['slug' => 'toko-budi'],
                [
                    'owner_id' => $owner->id,
                    'name' => 'Toko Budi',
                    'phone' => '08123456789',
                    'address' => 'Jl. Contoh No. 1, Malang',
                    'operational_start_time' => '07:00',
                    'operational_end_time' => '22:00',
                    'qris_payload' => '00020101021126670016COM.NOBUBANK.WWW01189360050300000879140214531234567890180303UMI51440014ID.CO.QRIS.WWW0215ID10200255943330303UMI5204541153033605802ID5909TOKO BUDI6011KOTA MALANG61056514162070703A01',
                    'member_discount_is_active' => true,
                    'member_discount_type' => 'percent',
                    'member_discount_value' => 10,
                    'member_discount_max_amount' => 20000,
                    'member_discount_min_purchase' => 50000,
                    'is_active' => true,
                ],
            );

            User::updateOrCreate(
                ['email' => 'kasir@tokobudi.com'],
                [
                    'name' => 'Siti Kasir',
                    'password' => Hash::make('kasir123'),
                    'is_active' => true,
                    'role' => 'cashier',
                    'merchant_id' => $merchant->id,
                ],
            );

            User::updateOrCreate(
                ['email' => 'kasir2@tokobudi.com'],
                [
                    'name' => 'Andi Kasir',
                    'password' => Hash::make('kasir123'),
                    'is_active' => true,
                    'role' => 'cashier',
                    'merchant_id' => $merchant->id,
                ],
            );

            $categories = collect([
                ['name' => 'Sembako', 'description' => 'Bahan pokok kebutuhan dapur.'],
                ['name' => 'Makanan Ringan', 'description' => 'Camilan dan makanan kemasan.'],
                ['name' => 'Minuman & Produk Seduh', 'description' => 'Minuman siap saji dan produk seduh.'],
                ['name' => 'Kebutuhan Mandi & Kebersihan', 'description' => 'Produk kebersihan rumah tangga dan perawatan diri.'],
                ['name' => 'Bumbu & Pelengkap Dapur', 'description' => 'Bumbu masak dan pelengkap dapur.'],
            ])->mapWithKeys(fn(array $data): array => [
                $data['name'] => Category::updateOrCreate(
                    ['merchant_id' => $merchant->id, 'name' => $data['name']],
                    ['description' => $data['description']],
                ),
            ]);

            $suppliers = collect([
                ['name' => 'CV Sumber Pangan', 'phone' => '081111111001', 'email' => 'sales@sumberpangan.test', 'address' => 'Jl. Pasar Besar No. 10, Malang'],
                ['name' => 'UD Camilan Jaya', 'phone' => '081111111002', 'email' => 'admin@camilanjaya.test', 'address' => 'Jl. Industri No. 8, Malang'],
                ['name' => 'PT Bersih Sejahtera', 'phone' => '081111111003', 'email' => 'order@bersihsejahtera.test', 'address' => 'Jl. Raya Gadang No. 21, Malang'],
            ])->mapWithKeys(fn(array $data): array => [
                $data['name'] => Supplier::updateOrCreate(
                    ['merchant_id' => $merchant->id, 'name' => $data['name']],
                    [
                        'phone' => $data['phone'],
                        'email' => $data['email'],
                        'address' => $data['address'],
                        'notes' => 'Supplier demo SiKaMo.',
                        'is_active' => true,
                    ],
                ),
            ]);

            $productRows = [
                ['name' => 'Beras Premium 5kg', 'barcode' => '8991234560012', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 64000, 'price' => 75000, 'opening' => 12, 'restock' => 20, 'sold' => 4],
                ['name' => 'Minyak Goreng 2L', 'barcode' => '8991234560029', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 31000, 'price' => 36500, 'opening' => 10, 'restock' => 18, 'sold' => 5],
                ['name' => 'Gula Pasir 1kg', 'barcode' => '8991234560036', 'category' => 'Sembako', 'supplier' => 'CV Sumber Pangan', 'cost' => 14500, 'price' => 17500, 'opening' => 20, 'restock' => 30, 'sold' => 6],
                ['name' => 'Indomie Goreng', 'barcode' => '8991234560043', 'category' => 'Makanan Ringan', 'supplier' => 'CV Sumber Pangan', 'cost' => 2800, 'price' => 3500, 'opening' => 60, 'restock' => 80, 'sold' => 18],
                ['name' => 'Keripik Singkong', 'barcode' => '8991234560050', 'category' => 'Makanan Ringan', 'supplier' => 'UD Camilan Jaya', 'cost' => 11000, 'price' => 15000, 'opening' => 15, 'restock' => 25, 'sold' => 7],
                ['name' => 'Oreo Vanilla 133g', 'barcode' => '8991234560067', 'category' => 'Makanan Ringan', 'supplier' => 'UD Camilan Jaya', 'cost' => 8200, 'price' => 10500, 'opening' => 25, 'restock' => 30, 'sold' => 8],
                ['name' => 'Aqua Galon 19L', 'barcode' => '8991234560074', 'category' => 'Minuman & Produk Seduh', 'supplier' => 'CV Sumber Pangan', 'cost' => 18000, 'price' => 22000, 'opening' => 8, 'restock' => 10, 'sold' => 3],
                ['name' => 'Kopi Kapal Api Mix', 'barcode' => '8991234560098', 'category' => 'Minuman & Produk Seduh', 'supplier' => 'CV Sumber Pangan', 'cost' => 11200, 'price' => 14500, 'opening' => 30, 'restock' => 40, 'sold' => 9],
                ['name' => 'Sabun Lifebuoy', 'barcode' => '8991234560111', 'category' => 'Kebutuhan Mandi & Kebersihan', 'supplier' => 'PT Bersih Sejahtera', 'cost' => 3800, 'price' => 5000, 'opening' => 40, 'restock' => 50, 'sold' => 10],
                ['name' => 'Rinso Bubuk 800g', 'barcode' => '8991234560128', 'category' => 'Kebutuhan Mandi & Kebersihan', 'supplier' => 'PT Bersih Sejahtera', 'cost' => 27000, 'price' => 32000, 'opening' => 12, 'restock' => 18, 'sold' => 4],
                ['name' => 'Kecap Bango 550ml', 'barcode' => '8991234560142', 'category' => 'Bumbu & Pelengkap Dapur', 'supplier' => 'CV Sumber Pangan', 'cost' => 20000, 'price' => 24000, 'opening' => 14, 'restock' => 20, 'sold' => 3],
                ['name' => 'Garam Dapur', 'barcode' => '8991234560159', 'category' => 'Bumbu & Pelengkap Dapur', 'supplier' => 'CV Sumber Pangan', 'cost' => 2100, 'price' => 3000, 'opening' => 35, 'restock' => 50, 'sold' => 12],
            ];

            $products = collect($productRows)->mapWithKeys(function (array $data) use ($merchant, $categories, $suppliers): array {
                $currentStock = $data['opening'] + $data['restock'] - $data['sold'];

                $product = Product::updateOrCreate(
                    ['merchant_id' => $merchant->id, 'barcode' => $data['barcode']],
                    [
                        'supplier_id' => $suppliers[$data['supplier']]->id,
                        'category_id' => $categories[$data['category']]->id,
                        'name' => $data['name'],
                        'price' => $data['price'],
                        'cost_price' => $data['cost'],
                        'stock' => $currentStock,
                        'low_stock_threshold' => 10,
                    ],
                );

                return [$data['barcode'] => ['model' => $product, 'seed' => $data]];
            });

            $productIds = $products->pluck('model.id')->all();
            StockLog::whereIn('product_id', $productIds)->where('note', 'like', 'Seeder demo:%')->delete();
            Transaction::whereIn('invoice_number', ['INV-DEMO-001', 'INV-DEMO-002', 'INV-DEMO-003'])->delete();
            Purchase::whereIn('invoice_number', ['PO-DEMO-001', 'PO-DEMO-002'])->delete();
            DailyReport::where('merchant_id', $merchant->id)->whereDate('report_date', now()->toDateString())->delete();

            $members = collect([
                ['name' => 'Rina Amelia', 'phone' => '081234560001', 'email' => 'rina@example.test', 'address' => 'Jl. Ijen No. 7, Malang'],
                ['name' => 'Dimas Pratama', 'phone' => '081234560002', 'email' => 'dimas@example.test', 'address' => 'Jl. Soekarno Hatta No. 12, Malang'],
                ['name' => 'Maya Lestari', 'phone' => '081234560003', 'email' => 'maya@example.test', 'address' => 'Jl. Veteran No. 5, Malang'],
            ])->mapWithKeys(fn(array $data): array => [
                $data['phone'] => Member::updateOrCreate(
                    ['merchant_id' => $merchant->id, 'phone' => $data['phone']],
                    [
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'address' => $data['address'],
                        'joined_at' => now()->subMonths(2)->toDateString(),
                        'notes' => 'Member demo SiKaMo.',
                        'is_active' => true,
                    ],
                ),
            ]);

            Discount::updateOrCreate(
                ['merchant_id' => $merchant->id, 'code' => 'HEMAT15'],
                [
                    'name' => 'Diskon 15% Maksimal 30 Ribu',
                    'type' => 'percent',
                    'value' => 15,
                    'max_discount_amount' => 30000,
                    'min_purchase_amount' => 50000,
                    'quota' => 100,
                    'used_count' => 1,
                    'starts_at' => now()->subWeek(),
                    'ends_at' => now()->addMonth(),
                    'is_active' => true,
                ],
            );

            Discount::updateOrCreate(
                ['merchant_id' => $merchant->id, 'code' => 'POTONG5000'],
                [
                    'name' => 'Potongan Langsung Rp5.000',
                    'type' => 'fixed',
                    'value' => 5000,
                    'max_discount_amount' => null,
                    'min_purchase_amount' => 30000,
                    'quota' => null,
                    'used_count' => 0,
                    'starts_at' => now()->subWeek(),
                    'ends_at' => now()->addMonth(),
                    'is_active' => true,
                ],
            );

            foreach ($products as $entry) {
                $product = $entry['model'];
                $seed = $entry['seed'];

                StockLog::create([
                    'product_id' => $product->id,
                    'user_id' => $owner->id,
                    'quantity_change' => $seed['opening'],
                    'stock_before' => 0,
                    'stock_after' => $seed['opening'],
                    'type' => 'in',
                    'note' => 'Seeder demo: stok awal produk',
                    'created_at' => now()->subDays(10),
                    'updated_at' => now()->subDays(10),
                ]);
            }

            $purchaseOne = $this->createPurchase(
                $merchant,
                $owner,
                $suppliers['CV Sumber Pangan'],
                'PO-DEMO-001',
                [
                    ['barcode' => '8991234560012', 'quantity' => 20],
                    ['barcode' => '8991234560029', 'quantity' => 18],
                    ['barcode' => '8991234560036', 'quantity' => 30],
                    ['barcode' => '8991234560043', 'quantity' => 80],
                ],
                $products,
            );

            $purchaseTwo = $this->createPurchase(
                $merchant,
                $owner,
                $suppliers['PT Bersih Sejahtera'],
                'PO-DEMO-002',
                [
                    ['barcode' => '8991234560111', 'quantity' => 50],
                    ['barcode' => '8991234560128', 'quantity' => 18],
                ],
                $products,
            );

            $this->createPurchaseStockLogs($purchaseOne, $products);
            $this->createPurchaseStockLogs($purchaseTwo, $products);

            $cashier = User::where('email', 'kasir@tokobudi.com')->firstOrFail();
            $discount = Discount::where('merchant_id', $merchant->id)->where('code', 'HEMAT15')->firstOrFail();

            $this->createTransaction(
                'INV-DEMO-001',
                $cashier,
                $members['081234560001'],
                $discount,
                'cash',
                [
                    ['barcode' => '8991234560012', 'quantity' => 2],
                    ['barcode' => '8991234560029', 'quantity' => 1],
                    ['barcode' => '8991234560043', 'quantity' => 5],
                ],
                $products,
            );

            $this->createTransaction(
                'INV-DEMO-002',
                $cashier,
                null,
                null,
                'qris',
                [
                    ['barcode' => '8991234560050', 'quantity' => 3],
                    ['barcode' => '8991234560067', 'quantity' => 2],
                    ['barcode' => '8991234560159', 'quantity' => 6],
                ],
                $products,
            );

            $this->createTransaction(
                'INV-DEMO-003',
                $cashier,
                $members['081234560002'],
                null,
                'cash',
                [
                    ['barcode' => '8991234560111', 'quantity' => 4],
                    ['barcode' => '8991234560128', 'quantity' => 1],
                    ['barcode' => '8991234560142', 'quantity' => 2],
                ],
                $products,
            );

            $todayTransactions = Transaction::whereIn('invoice_number', ['INV-DEMO-001', 'INV-DEMO-002', 'INV-DEMO-003'])->get();

            DailyReport::create([
                'merchant_id' => $merchant->id,
                'report_date' => now()->toDateString(),
                'total_transactions' => $todayTransactions->count(),
                'total_revenue' => $todayTransactions->sum('total_amount'),
                'total_items_sold' => TransactionItem::whereIn('transaction_id', $todayTransactions->pluck('id'))->sum('quantity'),
                'sent_to_whatsapp' => false,
                'sent_at' => null,
            ]);
        });

        $this->command?->info('DemoDataSeeder selesai. Login: admin@sikamo.com/admin123, owner@tokobudi.com/owner123, kasir@tokobudi.com/kasir123.');
    }

    private function createPurchase(Merchant $merchant, User $owner, Supplier $supplier, string $invoiceNumber, array $items, $products): Purchase
    {
        $purchase = Purchase::create([
            'merchant_id' => $merchant->id,
            'supplier_id' => $supplier->id,
            'user_id' => $owner->id,
            'invoice_number' => $invoiceNumber,
            'purchase_date' => now()->subDays(5)->toDateString(),
            'total_amount' => 0,
            'status' => 'completed',
            'notes' => 'Seeder demo: pembelian/restock supplier.',
        ]);

        $total = 0;

        foreach ($items as $item) {
            $product = $products[$item['barcode']]['model'];
            $unitCost = (float) $product->cost_price;
            $subtotal = $unitCost * $item['quantity'];
            $total += $subtotal;

            PurchaseItem::create([
                'purchase_id' => $purchase->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_cost' => $unitCost,
                'subtotal' => $subtotal,
            ]);
        }

        $purchase->forceFill(['total_amount' => $total])->save();

        return $purchase;
    }

    private function createPurchaseStockLogs(Purchase $purchase, $products): void
    {
        foreach ($purchase->items as $item) {
            $seed = $products->first(fn(array $entry): bool => $entry['model']->id === $item->product_id)['seed'];
            $stockBefore = $seed['opening'];

            StockLog::create([
                'product_id' => $item->product_id,
                'user_id' => $purchase->user_id,
                'quantity_change' => $item->quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockBefore + $item->quantity,
                'type' => 'in',
                'note' => 'Seeder demo: pembelian ' . $purchase->invoice_number,
                'created_at' => now()->subDays(5),
                'updated_at' => now()->subDays(5),
            ]);
        }
    }

    private function createTransaction(string $invoiceNumber, User $cashier, ?Member $member, ?Discount $discount, string $paymentMethod, array $items, $products): Transaction
    {
        $subtotal = 0;
        $totalCost = 0;

        foreach ($items as $item) {
            $product = $products[$item['barcode']]['model'];
            $subtotal += ((float) $product->price) * $item['quantity'];
            $totalCost += ((float) $product->cost_price) * $item['quantity'];
        }

        $voucherDiscount = $discount ? $discount->calculateDiscount($subtotal) : 0;
        $memberDiscount = $member ? min(($subtotal - $voucherDiscount) * 0.10, 20000) : 0;
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
            'created_at' => now()->subHours(3),
            'updated_at' => now()->subHours(3),
        ]);

        $allocatedDiscount = 0;
        $lastIndex = count($items) - 1;

        foreach ($items as $index => $item) {
            $product = $products[$item['barcode']]['model'];
            $itemSubtotal = ((float) $product->price) * $item['quantity'];
            $itemCost = ((float) $product->cost_price) * $item['quantity'];
            $itemDiscount = $index === $lastIndex
                ? $totalDiscount - $allocatedDiscount
                : round($totalDiscount * ($itemSubtotal / $subtotal), 2);

            $allocatedDiscount += $itemDiscount;

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'unit_price' => $product->price,
                'unit_cost' => $product->cost_price,
                'subtotal' => $itemSubtotal,
                'total_cost' => $itemCost,
                'gross_profit' => $itemSubtotal - $itemDiscount - $itemCost,
            ]);

            $seed = $products[$item['barcode']]['seed'];
            $stockBefore = $seed['opening'] + $seed['restock'];
            $previousSold = StockLog::query()
                ->where('product_id', $product->id)
                ->where('note', 'like', 'Seeder demo: penjualan%')
                ->sum(DB::raw('ABS(quantity_change)'));

            StockLog::create([
                'product_id' => $product->id,
                'user_id' => $cashier->id,
                'quantity_change' => -1 * $item['quantity'],
                'stock_before' => $stockBefore - $previousSold,
                'stock_after' => $stockBefore - $previousSold - $item['quantity'],
                'type' => 'out',
                'note' => 'Seeder demo: penjualan ' . $invoiceNumber,
                'created_at' => now()->subHours(3),
                'updated_at' => now()->subHours(3),
            ]);
        }

        return $transaction;
    }
}
