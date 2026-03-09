<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Create a default admin user (run after RoleSeeder).
     * Set ADMIN_EMAIL and ADMIN_PASSWORD in .env to customize.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@sastooffer.test');
        $password = env('ADMIN_PASSWORD', 'password');

        $admin = User::firstOrCreate(
            ['email' => $email],
            [
                'name'     => 'Admin',
                'phone'    => '0000000000',
                'password' => Hash::make($password),
                'status'   => 'active',
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }
    }
}
