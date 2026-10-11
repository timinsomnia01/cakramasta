<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Support\CatalogData;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Admin, verifikator, dan pembeli. Semua akun berpassword "password".
 * (Pemilik toko dibuat di SellerSeeder.)
 */
class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Administrator CakraMasta',
            'email' => 'admin@cakramasta.test',
            'password' => 'admincakra',
            'created_at' => now()->subDays(180),
            ]);
            
        foreach ([
            1 => 'Ratna Kusumawati',
            2 => 'Bambang Hermawan',
            ] as $number => $name) {
                User::factory()->verifikator()->create([
                    'name' => $name,
                    'email' => "verifikator{$number}@cakramasta.test",
                    'password' => 'verifcakra',
                    'created_at' => now()->subDays(170),
            ]);
        }

        foreach (CatalogData::buyerNames() as $index => $name) {
            User::factory()->create([
                'name' => $name,
                // Pembeli pertama dipakai sebagai akun demo: user@Cakra Masta.test
                'email' => $index === 0
                    ? 'user@cakramasta.test'
                    : Str::slug($name, '.').'@mahasiswa.cakramasta.test',
                'password' => 'mhscakra',
                'created_at' => now()->subDays(fake()->numberBetween(20, 140)),
            ]);
        }
    }
}
