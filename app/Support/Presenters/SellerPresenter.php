<?php

namespace App\Support\Presenters;

use App\Models\Seller;

/**
 * Mengubah model penjual menjadi array siap kirim ke komponen Inertia (sisi verifikator / admin).
 */
class SellerPresenter
{
    /**
     * @return array<string, mixed>
     */
    public static function row(Seller $seller): array
    {
        return [
            'id' => $seller->id,
            'storeName' => $seller->store_name,
            'slug' => $seller->slug,
            'ownerName' => $seller->relationLoaded('user') ? $seller->user?->name : null,
            'ownerEmail' => $seller->relationLoaded('user') ? $seller->user?->email : null,
            'phone' => $seller->phone,
            'status' => $seller->status->value,
            'statusLabel' => $seller->status->label(),
            'productsCount' => $seller->products_count ?? null,
            'ordersCount' => $seller->orders_count ?? null,
            'createdAt' => $seller->created_at->toISOString(),
        ];
    }

    /**
     * Detail lengkap, termasuk tautan dokumen sensitif yang dilayani lewat controller terotorisasi.
     *
     * @return array<string, mixed>
     */
    public static function detail(Seller $seller): array
    {
        $document = fn (string $type) => route('files.seller-document', ['seller' => $seller->id, 'type' => $type], absolute: false);

        return self::row($seller) + [
            'description' => $seller->description,
            'address' => $seller->address,
            'ktpNumber' => $seller->ktp_number,
            'bank' => $seller->bank_account_number ? [
                'name' => $seller->bank_name,
                'number' => $seller->bank_account_number,
                'holder' => $seller->bank_account_name,
            ] : null,
            'documents' => [
                'ktp' => $document('ktp'),
                'selfie' => $document('selfie'),
                'qris' => $document('qris'),
            ],
            'logo' => ProductPresenter::imageUrl($seller->logo),
            'rejectionReason' => $seller->rejection_reason,
            'verifiedBy' => $seller->relationLoaded('verifier') ? $seller->verifier?->name : null,
            'verifiedAt' => $seller->verified_at?->toISOString(),
        ];
    }
}
