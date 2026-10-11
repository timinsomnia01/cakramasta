<?php

namespace Database\Seeders;

use App\Enums\SellerStatus;
use App\Enums\UserRole;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\Support\CatalogData;
use Database\Seeders\Support\IndonesianData;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Pemilik toko beserta data registrasi penjual (KTP, selfie, QRIS).
 * Status campuran: disetujui, menunggu verifikasi, dan ditolak.
 */
class SellerSeeder extends Seeder
{
    public function run(): void
    {
        $verifiers = User::role(UserRole::Verifikator)->get()->all();

        foreach (CatalogData::sellers() as $index => $data) {
            $user = User::factory()->create([
                'name' => $data['owner'],
                'email' => Str::slug($data['owner'], '.').'@um-mart.test',
                'address' => IndonesianData::address($data['district']),
                'created_at' => now()->subDays(fake()->numberBetween(90, 150)),
            ]);

            $slug = Str::slug($data['store']);
            $nik = IndonesianData::nik(female: fake()->boolean());
            $bank = IndonesianData::bankAccount();
            $status = SellerStatus::from($data['status']);
            $verified = $status !== SellerStatus::Pending;
            $registeredAt = $user->created_at->copy()->addDays(fake()->numberBetween(1, 10));

            Seller::factory()->create([
                'user_id' => $user->id,
                'store_name' => $data['store'],
                'slug' => $slug,
                'description' => $data['description'],
                'phone' => $user->phone,
                'address' => $user->address,
                'logo' => PlaceholderImage::public("sellers/logos/{$slug}.svg", $data['store'], $index),
                'ktp_number' => $nik,
                'ktp_image' => PlaceholderImage::ktp("sellers/ktp/{$slug}.svg", $data['owner'], $nik),
                'selfie_image' => PlaceholderImage::selfie("sellers/selfie/{$slug}.svg", $data['owner']),
                'qris_image' => PlaceholderImage::qris("sellers/qris/{$slug}.svg", $data['store']),
                'bank_name' => $bank['bank'],
                'bank_account_number' => $bank['number'],
                'bank_account_name' => $data['owner'],
                'status' => $status,
                'rejection_reason' => $data['reason'] ?? null,
                'verified_by' => $verified ? fake()->randomElement($verifiers)->id : null,
                'verified_at' => $verified ? $registeredAt->copy()->addDays(fake()->numberBetween(1, 3)) : null,
                'created_at' => $registeredAt,
                'updated_at' => $registeredAt,
            ]);
        }
    }
}
