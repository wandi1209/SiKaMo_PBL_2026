<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $merchant = \App\Models\Merchant::where('slug', 'toko-budi')->first();
        if (! $merchant) {
            $this->command->error('Merchant "Toko Budi" tidak ditemukan. Jalankan UserSeeder terlebih dahulu.');
            return;
        }

        $categories = [
            ['name' => 'Sembako', 'description' => 'Bahan pokok kebutuhan dapur.'],
            ['name' => 'Makanan Ringan', 'description' => 'Berbagai jenis camilan kemasan.'],
            ['name' => 'Kebutuhan Mandi & Kebersihan', 'description' => 'Kelompok produk non-konsumsi yang digunakan untuk perawatan tubuh serta pemeliharaan kebersihan lingkungan rumah tangga.'],
            ['name' => 'Bumbu & Pelengkap Dapur', 'description' => 'Produk-produk tambahan yang berfungsi sebagai penguat rasa atau bahan pendukung dalam pengolahan masakan.'],
            ['name' => 'Minuman & Produk Seduh', 'description' => 'Produk konsumsi cair siap minum maupun produk berbentuk bubuk atau kering yang perlu dilarutkan sebelum dikonsumsi.'],
        ];

        foreach ($categories as $category) {
            $existingCategory = Category::where('name', $category['name'])
                ->where(function ($query) use ($merchant) {
                    $query->whereNull('merchant_id')
                        ->orWhere('merchant_id', $merchant->id);
                })
                ->first();

            if ($existingCategory) {
                $existingCategory->update([
                    'merchant_id' => $merchant->id,
                    'description' => $category['description'],
                ]);
            } else {
                Category::create([
                    'merchant_id' => $merchant->id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                ]);
            }
        }
    }
}
