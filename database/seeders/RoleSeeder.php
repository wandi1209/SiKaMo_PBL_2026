<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Define permissions
        $permissions = [
            // User management
            'view users',
            'create users',
            'edit users',
            'delete users',

            // Role management
            'view roles',
            'create roles',
            'edit roles',
            'delete roles',

            // Daily Report
            'view daily reports',
            'create daily reports',
            'edit daily reports',
            'delete daily reports',

            // WhatsApp Log
            'view whatsapp logs',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create roles and assign permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions(Permission::all());

        $operatorRole = Role::firstOrCreate(['name' => 'operator']);
        $operatorRole->syncPermissions([
            'view daily reports',
            'create daily reports',
            'edit daily reports',
            'view whatsapp logs',
        ]);
    }
}
