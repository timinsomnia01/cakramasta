<?php

namespace Database\Factories;

use App\Enums\WithdrawalStatus;
use App\Models\Seller;
use App\Models\User;
use App\Models\Withdrawal;
use Database\Seeders\Support\IndonesianData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $bank = IndonesianData::bankAccount();

        return [
            'code' => fn () => IndonesianData::code('WD', now()),
            'seller_id' => Seller::factory()->approved(),
            'amount' => fake()->numberBetween(5, 100) * 10000,
            'method' => 'bank_transfer',
            'qris_image' => null,
            'bank_name' => $bank['bank'],
            'bank_account_number' => $bank['number'],
            'bank_account_name' => fake('id_ID')->name(),
            'status' => WithdrawalStatus::Pending,
            'notes' => null,
            'rejection_reason' => null,
            'transfer_proof' => null,
            'processed_by' => null,
            'processed_at' => null,
        ];
    }

    /**
     * Indicate that the withdrawal has been approved and awaits transfer.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawalStatus::Approved,
            'processed_by' => User::factory()->verifikator(),
            'processed_at' => now(),
        ]);
    }

    /**
     * Indicate that the money has been transferred.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawalStatus::Paid,
            'processed_by' => User::factory()->verifikator(),
            'processed_at' => now(),
            'transfer_proof' => 'withdrawals/'.fake()->unique()->bothify('transfer-????####').'.svg',
        ]);
    }

    /**
     * Indicate that the withdrawal has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WithdrawalStatus::Rejected,
            'processed_by' => User::factory()->verifikator(),
            'processed_at' => now(),
            'rejection_reason' => 'Data rekening tidak sesuai dengan nama pemilik toko.',
        ]);
    }

    /**
     * Withdraw to the seller's QRIS instead of a bank account.
     */
    public function viaQris(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'qris',
            'qris_image' => 'sellers/qris/contoh.svg',
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_name' => null,
        ]);
    }
}
