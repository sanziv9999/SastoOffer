<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();


        $permissions = [
            // Deal management
            'view deals',
            'create deals',
            'edit deals',
            'delete deals',
            'publish deals',
            'unpublish deals',
            'feature deals',           // highlight/promote deal
            'approve deals',           // for moderation

            // Optional extras (uncomment as needed)
            // 'view deal analytics',
            // 'manage deal categories',
            // 'manage deal tags',
        ];

        // create permissions
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name]);
        }

        // // Super Admin (god mode)
        // $superAdmin = Role::firstOrCreate(['name' => 'super-admin']);
        // $superAdmin->syncPermissions(Permission::all()); // or list specific ones

        // Admin (can do almost everything except maybe delete users)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions([
            'view deals',
            'create deals',
            'edit deals',
            'delete deals',
            'publish deals',
            'unpublish deals',
            'feature deals',
            'approve deals',
        ]);

        // Vendor / Seller (owns their deals)
        $vendor = Role::firstOrCreate(['name' => 'vendor']);
        $vendor->syncPermissions([
            'view deals',
            'create deals',
            'edit deals',
            'delete deals',       // usually own deals only → check in policy
            // 'publish deals',   ← often not allowed (needs approval)
        ]);

        // Customer / Buyer (read-only + maybe favorites)
        $customer = Role::firstOrCreate(['name' => 'customer']);
        $customer->syncPermissions([
            'view deals',
            // 'create deals',    ← usually not
        ]);


        $user = \App\Models\User::factory()->create([
            'name' => 'Example User',
            'email' => 'tester@example.com',
            'phone' => '9860357514',
        ]);
        $user->assignRole($customer);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Admin User',
            'email' => 'admin@example.com',
            'phone' => '9860357512',
        ]);
        $user->assignRole($vendor);

        $user = \App\Models\User::factory()->create([
            'name' => 'Example Super-Admin User',
            'email' => 'superadmin@example.com',
            'phone' => '9860357513',
        ]);
        $user->assignRole($admin);
        
        


    }
}
