<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ReviewPayment
{
    /**
     * Setujui pembayaran: order menjadi "paid" dan penjual bisa mulai memproses.
     *
     * @throws ValidationException
     */
    public function approve(Payment $payment, User $verifier): Payment
    {
        return DB::transaction(function () use ($payment, $verifier) {
            $payment = $this->lock($payment);

            $payment->forceFill([
                'status' => PaymentStatus::Verified,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => null,
            ])->save();

            $payment->order->transitionTo(OrderStatus::Paid, $verifier, 'Pembayaran diverifikasi.');

            return $payment;
        });
    }

    /**
     * Tolak pembayaran: order kembali "menunggu pembayaran" dengan tagihan baru
     * (batas waktu diperpanjang) agar pembeli dapat mengunggah bukti yang benar.
     *
     * @throws ValidationException
     */
    public function reject(Payment $payment, User $verifier, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $verifier, $reason) {
            $payment = $this->lock($payment);

            $payment->forceFill([
                'status' => PaymentStatus::Rejected,
                'verified_by' => $verifier->id,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ])->save();

            $payment->order->payments()->create([
                'method' => $payment->method,
                'amount' => $payment->amount,
                'status' => PaymentStatus::Pending,
                'expires_at' => now()->addHours((int) config('marketplace.payment_expiry_hours')),
            ]);

            $payment->order->transitionTo(OrderStatus::PendingPayment, $verifier, 'Pembayaran ditolak: '.$reason);

            return $payment;
        });
    }

    /**
     * @throws ValidationException
     */
    private function lock(Payment $payment): Payment
    {
        $payment = Payment::query()->with('order')->lockForUpdate()->findOrFail($payment->id);

        if ($payment->status !== PaymentStatus::WaitingVerification || $payment->order->status !== OrderStatus::WaitingVerification) {
            throw ValidationException::withMessages(['payment' => 'Pembayaran ini sudah diproses.']);
        }

        return $payment;
    }
}
