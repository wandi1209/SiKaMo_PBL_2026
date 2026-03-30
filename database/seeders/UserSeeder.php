<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'admin@sikamo.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'is_active' => true,
            ]
        );

        $user->assignRole('admin');
    }
}
