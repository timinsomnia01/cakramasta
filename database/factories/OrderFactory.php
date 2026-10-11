<?php

namespace Database\Factories;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Seller;
use App\Models\User;
use Database\Seeders\Support\IndonesianData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Nominal order: total = subtotal + ongkir (dibayar pembeli),
 * seller_income = subtotal - biaya platform 5% - pajak final 0,5%.
 *
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fn () => IndonesianData::code('UM', now()),
            'user_id' => User::factory(),
            'seller_id' => Seller::factory()->approved(),
            'status' => OrderStatus::PendingPayment,
            'subtotal' => fake()->numberBetween(2, 400) * 1000,
            'shipping_cost' => fake()->randomElement([0, 8000, 10000, 12000, 15000]),
            'platform_fee' => fn (array $attributes) => (int) round($attributes['subtotal'] * 0.05),
            'tax_amount' => fn (array $attributes) => (int) round($attributes['subtotal'] * 0.005),
            'total' => fn (array $attributes) => $attributes['subtotal'] + $attributes['shipping_cost'],
            'seller_income' => fn (array $attributes) => $attributes['subtotal'] - $attributes['platform_fee'] - $attributes['tax_amount'],
            'recipient_name' => fake('id_ID')->name(),
            'recipient_phone' => IndonesianData::phone(),
            'shipping_address' => IndonesianData::address(),
            'courier' => null,
            'tracking_number' => null,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the payment has been uploaded and is waiting for verification.
     */
    public function waitingVerification(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::WaitingVerification,
        ]);
    }

    /**
     * Indicate that the order has been paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Paid,
            'paid_at' => now()->subDay(),
        ]);
    }

    /**
     * Indicate that the order is being processed by the seller.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Processing,
            'paid_at' => now()->subDays(2),
        ]);
    }

    /**
     * Indicate that the order has been shipped.
     */
    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Shipped,
            'courier' => 'JNE Reguler',
            'tracking_number' => fake()->numerify('JNE##########'),
            'paid_at' => now()->subDays(4),
            'shipped_at' => now()->subDays(2),
        ]);
    }

    /**
     * Indicate that the order has been completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Completed,
            'courier' => 'J&T Express',
            'tracking_number' => fake()->numerify('JP##########'),
            'paid_at' => now()->subDays(10),
            'shipped_at' => now()->subDays(8),
            'completed_at' => now()->subDays(5),
        ]);
    }

    /**
     * Indicate that the order has been cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => OrderStatus::Cancelled,
            'cancelled_at' => now(),
            'cancel_reason' => 'Pembayaran tidak dilakukan dalam batas waktu.',
        ]);
    }
}
