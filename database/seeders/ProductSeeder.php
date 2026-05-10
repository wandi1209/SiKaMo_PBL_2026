<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the merchant (Toko Budi)
        $merchant = \App\Models\Merchant::where('slug', 'toko-budi')->first();
        if (!$merchant) {
            $this->command->error('Merchant "Toko Budi" tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $data = [
            ['name' => 'Beras Premium 5kg', 'barcode' => '8991234560012', 'price' => 75000, 'stock' => 25, 'category' => 'Sembako'],
            ['name' => 'Minyak Goreng 2L', 'barcode' => '8991234560029', 'price' => 36500, 'stock' => 15, 'category' => 'Sembako'],
            ['name' => 'Gula Pasir 1kg', 'barcode' => '8991234560036', 'price' => 17500, 'stock' => 40, 'category' => 'Sembako'],
            ['name' => 'Indomie Goreng', 'barcode' => '8991234560043', 'price' => 3500, 'stock' => 120, 'category' => 'Makanan Ringan'],
            ['name' => 'Keripik Singkong', 'barcode' => '8991234560050', 'price' => 15000, 'stock' => 30, 'category' => 'Makanan Ringan'],
            ['name' => 'Oreo Vanilla 133g', 'barcode' => '8991234560067', 'price' => 10500, 'stock' => 50, 'category' => 'Makanan Ringan'],
            ['name' => 'Aqua Galon 19L', 'barcode' => '8991234560074', 'price' => 22000, 'stock' => 12, 'category' => 'Minuman & Produk Seduh'],
            ['name' => 'Coca Cola 1.5L', 'barcode' => '8991234560081', 'price' => 16000, 'stock' => 20, 'category' => 'Minuman & Produk Seduh'],
            ['name' => 'Kopi Kapal Api Mix', 'barcode' => '8991234560098', 'price' => 14500, 'stock' => 60, 'category' => 'Minuman & Produk Seduh'],
            ['name' => 'Susu Frisian Flag', 'barcode' => '8991234560104', 'price' => 18000, 'stock' => 24, 'category' => 'Minuman & Produk Seduh'],
            ['name' => 'Sabun Lifebuoy', 'barcode' => '8991234560111', 'price' => 5000, 'stock' => 100, 'category' => 'Kebutuhan Mandi & Kebersihan'],
            ['name' => 'Rinso Bubuk 800g', 'barcode' => '8991234560128', 'price' => 32000, 'stock' => 18, 'category' => 'Kebutuhan Mandi & Kebersihan'],
            ['name' => 'Pepsodent 190g', 'barcode' => '8991234560135', 'price' => 16500, 'stock' => 45, 'category' => 'Kebutuhan Mandi & Kebersihan'],
            ['name' => 'Kecap Bango 550ml', 'barcode' => '8991234560142', 'price' => 24000, 'stock' => 30, 'category' => 'Bumbu & Pelengkap Dapur'],
            ['name' => 'Garam Dapur', 'barcode' => '8991234560159', 'price' => 3000, 'stock' => 80, 'category' => 'Bumbu & Pelengkap Dapur'],
        ];

        foreach ($data as $item) {
            $category = Category::where('name', $item['category'])->first();

            if (!$category) {
                $this->command->warn("Kategori '{$item['category']}' tidak ditemukan untuk produk '{$item['name']}'. Melewati...");
                continue;
            }

            Product::updateOrCreate(
                ['barcode' => $item['barcode']],
                [
                    'merchant_id' => $merchant->id,
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'stock' => $item['stock'],
                    'low_stock_threshold' => 10,
                ]
            );
        }

        $this->command->info('ProductSeeder selesai. ' . count($data) . ' produk berhasil dibuat.');
    }
}
