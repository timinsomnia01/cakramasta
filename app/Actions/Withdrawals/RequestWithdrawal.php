<?php

namespace App\Actions\Withdrawals;

use App\Models\Seller;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RequestWithdrawal
{
    /**
     * Ajukan pencairan hasil jual. Baris penjual dikunci selama transaksi agar dua pengajuan
     * bersamaan tidak bisa melewati saldo yang tersedia.
     *
     * @throws ValidationException
     */
    public function handle(Seller $seller, int $amount, string $method, ?string $notes = null): Withdrawal
    {
        return DB::transaction(function () use ($seller, $amount, $method, $notes) {
            $seller = Seller::query()->lockForUpdate()->findOrFail($seller->id);

            $minimum = (int) config('marketplace.min_withdrawal');

            if ($amount < $minimum) {
                throw ValidationException::withMessages([
                    'amount' => 'Minimum pencairan Rp'.number_format($minimum, 0, ',', '.').'.',
                ]);
            }

            $balance = $seller->availableBalance();

            if ($amount > $balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo tersedia hanya Rp'.number_format($balance, 0, ',', '.').'.',
                ]);
            }

            $viaQris = $method === 'qris';

            return $seller->withdrawals()->create([
                'amount' => $amount,
                'method' => $method,
                'qris_image' => $viaQris ? $seller->qris_image : null,
                'bank_name' => $viaQris ? null : $seller->bank_name,
                'bank_account_number' => $viaQris ? null : $seller->bank_account_number,
                'bank_account_name' => $viaQris ? null : $seller->bank_account_name,
                'notes' => $notes,
            ]);
        });
    }
}
