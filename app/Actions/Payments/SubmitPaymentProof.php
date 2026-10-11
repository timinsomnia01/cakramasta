<?php

namespace App\Actions\Payments;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SubmitPaymentProof
{
    /**
     * Simpan bukti pembayaran (disk privat) dan ajukan ke verifikator.
     *
     * @throws ValidationException
     */
    public function handle(Order $order, User $by, UploadedFile $proof): Order
    {
        return DB::transaction(function () use ($order, $by, $proof) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== OrderStatus::PendingPayment) {
                throw ValidationException::withMessages(['proof' => 'Pesanan ini tidak sedang menunggu pembayaran.']);
            }

            $payment = $order->payments()->where('status', PaymentStatus::Pending)->latest('id')->first();

            if (! $payment || $payment->isExpired()) {
                throw ValidationException::withMessages(['proof' => 'Batas waktu pembayaran sudah lewat.']);
            }

            $path = $proof->store('payment-proofs', 'local');

            if ($payment->proof_image) {
                Storage::disk('local')->delete($payment->proof_image);
            }

            $payment->forceFill([
                'proof_image' => $path,
                'status' => PaymentStatus::WaitingVerification,
                'paid_at' => now(),
            ])->save();

            $order->transitionTo(OrderStatus::WaitingVerification, $by, 'Bukti pembayaran diunggah.');

            return $order;
        });
    }
}
