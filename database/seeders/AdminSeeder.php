<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@lensmart.test'],
            [
                'name' => 'Admin LensMart',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
                'phone' => '081100000001',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );
    }
}
