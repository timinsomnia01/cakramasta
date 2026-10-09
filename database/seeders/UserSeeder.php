<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\VerificationStatus;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password semua akun demo: "password"
        $admin = User::create([
            'name' => 'Admin UM-Mart',
            'email' => 'admin@umart.test',
            'password' => 'password',
            'role' => UserRole::Admin,
            'email_verified_at' => now(),
        ]);

        $verifikator = User::create([
            'name' => 'Verifikator UM-Mart',
            'email' => 'verifikator@umart.test',
            'password' => 'password',
            'role' => UserRole::Verifikator,
            'email_verified_at' => now(),
        ]);

        // Penjual yang sudah disetujui
        $sellers = [
            ['name' => 'Budi Santoso', 'email' => 'penjual1@umart.test', 'store' => 'Toko Budi Elektronik'],
            ['name' => 'Siti Aminah', 'email' => 'penjual2@umart.test', 'store' => 'Siti Fashion & Kain'],
        ];

        foreach ($sellers as $i => $s) {
            $user = User::create([
                'name' => $s['name'],
                'email' => $s['email'],
                'password' => 'password',
                'role' => UserRole::User,
                'phone' => '08123456780'.$i,
                'email_verified_at' => now(),
            ]);

            SellerProfile::create([
                'user_id' => $user->id,
                'store_name' => $s['store'],
                'slug' => Str::slug($s['store']),
                'store_description' => 'Menjual produk berkualitas untuk civitas kampus.',
                'nik' => '35730100000000'.str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT),
                'ktp_photo' => 'seed/sellers/ktp.jpg',
                'selfie_photo' => 'seed/sellers/selfie.jpg',
                'qris_image' => 'seed/sellers/qris.png',
                'status' => VerificationStatus::Approved,
                'verified_by' => $verifikator->id,
                'verified_at' => now()->subDays(10),
            ]);
        }

        // Calon penjual (menunggu verifikasi) -> untuk demo halaman verifikasi penjual
        $pending = User::create([
            'name' => 'Calon Penjual',
            'email' => 'calonpenjual@umart.test',
            'password' => 'password',
            'role' => UserRole::User,
            'email_verified_at' => now(),
        ]);

        SellerProfile::create([
            'user_id' => $pending->id,
            'store_name' => 'Toko Calon Penjual',
            'slug' => 'toko-calon-penjual',
            'nik' => '3573010000000099',
            'ktp_photo' => 'seed/sellers/ktp.jpg',
            'selfie_photo' => 'seed/sellers/selfie.jpg',
            'qris_image' => 'seed/sellers/qris.png',
            'status' => VerificationStatus::Pending,
        ]);

        // Pembeli biasa
        foreach (range(1, 6) as $n) {
            User::create([
                'name' => "Pembeli {$n}",
                'email' => "pembeli{$n}@umart.test",
                'password' => 'password',
                'role' => UserRole::User,
                'email_verified_at' => now(),
            ]);
        }
    }
}
