<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Catatan: WithoutModelEvents mematikan event model, jadi slug dan kode (invoice, withdrawal)
     * tidak dibuat otomatis oleh boot() model. Semua seeder di bawah mengisinya secara eksplisit.
     */
    public function run(): void
    {
        // Seed Faker agar data demo selalu sama setiap kali migrate:fresh --seed.
        fake()->seed(20261010);

        $this->call([
            UserSeeder::class,
            SellerSeeder::class,
            CategorySeeder::class,
            ProductSeeder::class,
            InteractionSeeder::class,
            OrderSeeder::class,
            WithdrawalSeeder::class,
            PageSeeder::class,
            VisitSeeder::class,
        ]);
    }
}
