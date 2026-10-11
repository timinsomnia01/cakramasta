<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\Payment;
use App\Models\Seller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Melayani berkas sensitif yang disimpan di disk privat (KTP, selfie, QRIS, bukti bayar, bukti transfer).
 * Berkas ini tidak pernah punya URL publik; setiap akses diperiksa per pengguna.
 */
class PrivateFileController extends Controller
{
    private const SELLER_DOCUMENTS = [
        'ktp' => 'ktp_image',
        'selfie' => 'selfie_image',
        'qris' => 'qris_image',
    ];

    /**
     * KTP & selfie: pemilik toko dan staf. QRIS: ditambah admin / verifikator untuk proses pencairan.
     */
    public function sellerDocument(Request $request, Seller $seller, string $type): StreamedResponse
    {
        abort_unless(isset(self::SELLER_DOCUMENTS[$type]), 404);

        $user = $request->user();
        abort_unless($seller->user_id === $user->id || $this->isStaff($request), 403);

        return $this->respond($seller->{self::SELLER_DOCUMENTS[$type]});
    }

    /**
     * Bukti pembayaran: pembeli, penjual pemilik order, dan staf.
     */
    public function paymentProof(Request $request, Payment $payment): StreamedResponse
    {
        $payment->loadMissing('order.seller');
        $user = $request->user();

        abort_unless(
            $payment->order->user_id === $user->id
                || $payment->order->seller?->user_id === $user->id
                || $this->isStaff($request),
            403,
        );

        return $this->respond($payment->proof_image);
    }

    /**
     * Bukti transfer pencairan: pemilik toko dan staf.
     */
    public function withdrawalProof(Request $request, Withdrawal $withdrawal): StreamedResponse
    {
        $withdrawal->loadMissing('seller');

        abort_unless($withdrawal->seller->user_id === $request->user()->id || $this->isStaff($request), 403);

        return $this->respond($withdrawal->transfer_proof);
    }

    private function isStaff(Request $request): bool
    {
        return $request->user()->hasRole(UserRole::Admin, UserRole::Verifikator);
    }

    private function respond(?string $path): StreamedResponse
    {
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
