<?php

namespace App\Http\Controllers\Account;

use App\Actions\Sellers\RegisterSeller;
use App\Enums\SellerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\SellerRegistrationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SellerRegistrationController extends Controller
{
    /**
     * Status pendaftaran penjual dan formulir (kosong, atau terisi ulang bila ditolak).
     * Berkas dokumen tidak pernah dikirim ke browser; hanya penanda bahwa berkas sudah ada.
     */
    public function show(Request $request): Response
    {
        $seller = $request->user()->seller;
        $user = $request->user();

        return Inertia::render('account/seller-registration', [
            'seller' => $seller ? [
                'storeName' => $seller->store_name,
                'status' => $seller->status->value,
                'statusLabel' => $seller->status->label(),
                'rejectionReason' => $seller->rejection_reason,
                'submittedAt' => $seller->updated_at?->toISOString(),
            ] : null,
            'form' => [
                'store_name' => $seller?->store_name ?? '',
                'description' => $seller?->description ?? '',
                'phone' => $seller?->phone ?? $user->phone ?? '',
                'address' => $seller?->address ?? $user->address ?? '',
                'ktp_number' => $seller?->ktp_number ?? '',
                'bank_name' => $seller?->bank_name ?? '',
                'bank_account_number' => $seller?->bank_account_number ?? '',
                'bank_account_name' => $seller?->bank_account_name ?? '',
            ],
            'canSubmit' => $seller === null || $seller->status === SellerStatus::Rejected,
            'hasDocuments' => $seller !== null,
        ]);
    }

    public function store(SellerRegistrationRequest $request, RegisterSeller $register): RedirectResponse
    {
        $register->handle(
            $request->user(),
            $request->user()->seller,
            $request->safe()->except(['ktp_image', 'selfie_image', 'qris_image', 'agree']),
            [
                'ktp_image' => $request->file('ktp_image'),
                'selfie_image' => $request->file('selfie_image'),
                'qris_image' => $request->file('qris_image'),
            ],
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pendaftaran toko terkirim. Menunggu verifikasi.')]);

        return to_route('account.seller.show');
    }
}
