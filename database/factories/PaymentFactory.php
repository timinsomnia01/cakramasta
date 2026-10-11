<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'method' => fake()->randomElement(['qris', 'bank_transfer']),
            'amount' => fake()->numberBetween(10, 500) * 1000,
            'status' => PaymentStatus::Pending,
            'proof_image' => null,
            'verified_by' => null,
            'verified_at' => null,
            'rejection_reason' => null,
            'gateway_reference' => null,
            'gateway_payload' => null,
            'paid_at' => null,
            'expires_at' => now()->addDay(),
        ];
    }

    /**
     * Indicate that the proof has been uploaded and is waiting for verification.
     */
    public function waitingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::WaitingVerification,
            'proof_image' => 'payments/proofs/'.fake()->unique()->bothify('bukti-????####').'.svg',
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the payment has been verified.
     */
    public function verified(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Verified,
            'proof_image' => 'payments/proofs/'.fake()->unique()->bothify('bukti-????####').'.svg',
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now(),
            'paid_at' => now()->subHour(),
        ]);
    }

    /**
     * Indicate that the payment has been rejected.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Rejected,
            'proof_image' => 'payments/proofs/'.fake()->unique()->bothify('bukti-????####').'.svg',
            'verified_by' => User::factory()->verifikator(),
            'verified_at' => now(),
            'rejection_reason' => 'Nominal pada bukti pembayaran tidak sesuai tagihan.',
        ]);
    }

    /**
     * Indicate that the payment has expired.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PaymentStatus::Expired,
            'expires_at' => now()->subDay(),
        ]);
    }
}
