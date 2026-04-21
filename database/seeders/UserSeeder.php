<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super admin — pulled from ADMIN_EMAIL env var so it's environment-safe
        User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'info@expatcarbuyers.com')],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'changeme123!')),
                'role'     => 'super_admin',
            ]
        );

        // Dummy Admin
        User::firstOrCreate(
            ['email' => 'admin@expatcarbuyers.com'],
            [
                'name'     => 'Admin Test',
                'password' => Hash::make('admin123'),
                'role'     => 'super_admin',
            ]
        );

        // Dummy User
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name'     => 'John Doe',
                'password' => Hash::make('user123'),
                'role'     => 'user',
            ]
        );
    }
}
