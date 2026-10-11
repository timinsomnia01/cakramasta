<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\WithdrawalStatus;
use App\Models\Seller;
use App\Models\User;
use App\Models\Withdrawal;
use Database\Seeders\Support\IndonesianData;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Carbon\CarbonInterface;

/**
 * Pencairan hasil jual. Total yang diajukan selalu di bawah pendapatan penjual,
 * jadi Seller::availableBalance() tidak pernah negatif.
 */
class WithdrawalSeeder extends Seeder
{
    private const MINIMUM = 50000;

    public function run(): void
    {
        $verifiers = User::role(UserRole::Verifikator)->get()->all();

        Seller::approved()->orderBy('id')->get()->each(function (Seller $seller, int $index) use ($verifiers) {
            $income = $seller->totalIncome();

            if ($income < self::MINIMUM * 2) {
                return;
            }

            $verifier = fake()->randomElement($verifiers);

            // Pencairan pertama: sudah ditransfer (50% pendapatan).
            $this->create($seller, $index, $this->roundDown($income * 0.5), WithdrawalStatus::Paid, $verifier, now()->subDays(fake()->numberBetween(6, 14)));

            // Pencairan kedua (25% pendapatan): status bergantian agar semua status terlihat.
            $second = $this->roundDown($income * 0.25);
            if ($second >= self::MINIMUM) {
                $status = [WithdrawalStatus::Pending, WithdrawalStatus::Approved, WithdrawalStatus::Rejected][$index % 3];
                $this->create($seller, $index + 1, $second, $status, $verifier, now()->subDays(fake()->numberBetween(0, 3)));
            }
        });
    }

    private function create(Seller $seller, int $variant, int $amount, WithdrawalStatus $status, User $verifier, CarbonInterface $requestedAt): void
    {
        $code = IndonesianData::code('WD', $requestedAt);
        $viaQris = $variant % 2 === 1;
        $processed = $status !== WithdrawalStatus::Pending;
        $processedAt = $requestedAt->copy()->addHours(fake()->numberBetween(4, 36));

        Withdrawal::factory()->create([
            'code' => $code,
            'seller_id' => $seller->id,
            'amount' => $amount,
            'method' => $viaQris ? 'qris' : 'bank_transfer',
            'qris_image' => $viaQris ? $seller->qris_image : null,
            'bank_name' => $viaQris ? null : $seller->bank_name,
            'bank_account_number' => $viaQris ? null : $seller->bank_account_number,
            'bank_account_name' => $viaQris ? null : $seller->bank_account_name,
            'status' => $status,
            'notes' => fake()->boolean(30) ? 'Pencairan hasil penjualan bulan ini.' : null,
            'rejection_reason' => $status === WithdrawalStatus::Rejected
                ? 'Data rekening tidak sesuai dengan nama pemilik toko, mohon perbarui data pencairan.'
                : null,
            'transfer_proof' => $status === WithdrawalStatus::Paid
                ? PlaceholderImage::proof("withdrawals/{$code}.svg", $code, $amount, $viaQris ? 'qris' : 'bank_transfer')
                : null,
            'processed_by' => $processed ? $verifier->id : null,
            'processed_at' => $processed ? $processedAt : null,
            'created_at' => $requestedAt,
            'updated_at' => $processed ? $processedAt : $requestedAt,
        ]);
    }

    private function roundDown(float $amount): int
    {
        return (int) (floor($amount / 10000) * 10000);
    }
}
