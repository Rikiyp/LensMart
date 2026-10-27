<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminSeeder::class,
            MasterDataSeeder::class,
            PartnerSeeder::class,
            CustomerSeeder::class,
            ProductSeeder::class,
            DemoOrderSeeder::class,
        ]);
    }
}
