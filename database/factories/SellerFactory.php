<?php

namespace Database\Factories;

use App\Enums\SellerStatus;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\Support\IndonesianData;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Seller>
 */
class SellerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $store = 'Toko '.fake('id_ID')->unique()->lastName();
        $slug = Str::slug($store);
        $bank = IndonesianData::bankAccount();

        return [
            'user_id' => User::factory(),
            'store_name' => $store,
            'slug' => $slug,
            'description' => fake()->sentence(12),
            'phone' => IndonesianData::phone(),
            'address' => IndonesianData::address(),
            'logo' => null,
            'ktp_number' => IndonesianData::nik(),
            'ktp_image' => "sellers/ktp/{$slug}.svg",
            'selfie_image' => "sellers/selfie/{$slug}.svg",
            'qris_image' => "sellers/qris/{$slug}.svg",
            'bank_name' => $bank['bank'],
            'bank_account_number' => $bank['number'],
            'bank_account_name' => fake('id_ID')->name(),
            'status' => SellerStatus::Pending,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ];
    }

    /**
     * Indicate that the seller has been approved.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SellerStatus::Approved,
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    /**
     * Indicate that the seller has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SellerStatus::Rejected,
            'rejection_reason' => 'Dokumen registrasi tidak jelas, mohon unggah ulang.',
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now(),
        ]);
    }

    /**
     * Indicate that the seller has been suspended.
     */
    public function suspended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => SellerStatus::Suspended,
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now()->subDays(60),
        ]);
    }

    /**
     * Indicate that the seller has been deleted.
     */
    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
