<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Merchant;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ─── Admin (super-admin) ──────────────────────────────────
        $admin = User::firstOrCreate(
            ['email' => 'admin@sikamo.com'],
            [
                'name'      => 'Administrator',
                'password'  => Hash::make('admin123'),
                'is_active' => true,
                'role'      => 'admin',
            ]
        );

        // ─── Sample Owner + Merchant ──────────────────────────────
        $owner = User::firstOrCreate(
            ['email' => 'owner@tokobudi.com'],
            [
                'name'      => 'Budi Santoso',
                'password'  => Hash::make('owner123'),
                'is_active' => true,
                'role'      => 'owner',
            ]
        );

        // Buat merchant untuk owner ini (jika belum ada)
        Merchant::firstOrCreate(
            ['owner_id' => $owner->id],
            [
                'name'      => 'Toko Budi',
                'slug'      => 'toko-budi',
                'phone'     => '08123456789',
                'address'   => 'Jl. Contoh No. 1, Malang',
                'is_active' => true,
            ]
        );

        // ─── Sample Cashier ───────────────────────────────────────
        // Ambil merchant Toko Budi untuk memasangkan kasir
        $tokoBudi = \App\Models\Merchant::where('slug', 'toko-budi')->first();

        $cashier = User::firstOrCreate(
            ['email' => 'kasir@tokobudi.com'],
            [
                'name'        => 'Siti Kasir',
                'password'    => Hash::make('kasir123'),
                'is_active'   => true,
                'role'        => 'cashier',
                'merchant_id' => $tokoBudi?->id, // terhubung ke Toko Budi
            ]
        );
    }
}

