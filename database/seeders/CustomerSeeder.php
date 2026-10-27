<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $customers = [
            [
                'name' => 'Rina Kartika',
                'email' => 'customer@lensmart.test',
                'phone' => '081300000021',
                'address' => [
                    'label' => 'Rumah',
                    'recipient_name' => 'Rina Kartika',
                    'phone' => '081300000021',
                    'address_line' => 'Jl. Melati No. 8, RT 04/RW 02, Kebayoran Baru',
                    'city' => 'Jakarta Selatan',
                    'province' => 'DKI Jakarta',
                    'postal_code' => '12140',
                ],
            ],
            [
                'name' => 'Dimas Aditya',
                'email' => 'dimas@lensmart.test',
                'phone' => '081300000022',
                'address' => [
                    'label' => 'Kantor',
                    'recipient_name' => 'Dimas Aditya',
                    'phone' => '081300000022',
                    'address_line' => 'Jl. Asia Afrika No. 112, Sumur Bandung',
                    'city' => 'Bandung',
                    'province' => 'Jawa Barat',
                    'postal_code' => '40112',
                ],
            ],
            [
                'name' => 'Nadia Putri',
                'email' => 'nadia@lensmart.test',
                'phone' => '081300000023',
                'address' => [
                    'label' => 'Rumah',
                    'recipient_name' => 'Nadia Putri',
                    'phone' => '081300000023',
                    'address_line' => 'Jl. Raya Darmo No. 45, Wonokromo',
                    'city' => 'Surabaya',
                    'province' => 'Jawa Timur',
                    'postal_code' => '60241',
                ],
            ],
            [
                'name' => 'Fajar Nugroho',
                'email' => 'fajar@lensmart.test',
                'phone' => '081300000024',
                'address' => [
                    'label' => 'Rumah',
                    'recipient_name' => 'Fajar Nugroho',
                    'phone' => '081300000024',
                    'address_line' => 'Jl. Godean KM 4, Gamping',
                    'city' => 'Sleman',
                    'province' => 'DI Yogyakarta',
                    'postal_code' => '55294',
                ],
            ],
        ];

        foreach ($customers as $index => $customer) {
            $user = User::updateOrCreate(
                ['email' => $customer['email']],
                [
                    'name' => $customer['name'],
                    'password' => Hash::make('password'),
                    'role' => UserRole::Customer,
                    'phone' => $customer['phone'],
                    'email_verified_at' => now(),
                    'is_active' => true,
                ]
            );

            Address::updateOrCreate(
                ['user_id' => $user->id, 'label' => $customer['address']['label']],
                [
                    'recipient_name' => $customer['address']['recipient_name'],
                    'phone' => $customer['address']['phone'],
                    'address_line' => $customer['address']['address_line'],
                    'city' => $customer['address']['city'],
                    'province' => $customer['address']['province'],
                    'postal_code' => $customer['address']['postal_code'],
                    'is_default' => true,
                ]
            );
        }
    }
}
