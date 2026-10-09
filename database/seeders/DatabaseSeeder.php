<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            PageSeeder::class,
            UserSeeder::class,
            BannerSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,       // order, payment, log, review, withdrawal
            VisitorLogSeeder::class,
        ]);
    }
}
