<?php

namespace App\Support\Presenters;

use App\Models\Withdrawal;

/**
 * Mengubah model pencairan menjadi array siap kirim ke komponen Inertia.
 */
class WithdrawalPresenter
{
    /**
     * Relasi seller (dan processor bila perlu) harus sudah dimuat untuk menyertakan datanya.
     *
     * @return array<string, mixed>
     */
    public static function row(Withdrawal $withdrawal): array
    {
        return [
            'code' => $withdrawal->code,
            'amount' => $withdrawal->amount,
            'method' => $withdrawal->method,
            'methodLabel' => $withdrawal->method === 'qris' ? 'QRIS' : 'Transfer Bank',
            'status' => $withdrawal->status->value,
            'statusLabel' => $withdrawal->status->label(),
            'destination' => $withdrawal->method === 'qris' ? 'QRIS toko' : trim("{$withdrawal->bank_name} {$withdrawal->bank_account_number} a.n. {$withdrawal->bank_account_name}"),
            'bank' => $withdrawal->method === 'qris' ? null : [
                'name' => $withdrawal->bank_name,
                'number' => $withdrawal->bank_account_number,
                'holder' => $withdrawal->bank_account_name,
            ],
            'qrisUrl' => $withdrawal->method === 'qris' && $withdrawal->relationLoaded('seller')
                ? route('files.seller-document', ['seller' => $withdrawal->seller_id, 'type' => 'qris'], absolute: false)
                : null,
            'notes' => $withdrawal->notes,
            'rejectionReason' => $withdrawal->rejection_reason,
            'hasTransferProof' => $withdrawal->transfer_proof !== null,
            'transferProofUrl' => $withdrawal->transfer_proof ? route('files.withdrawal-proof', $withdrawal, absolute: false) : null,
            'storeName' => $withdrawal->relationLoaded('seller') ? $withdrawal->seller?->store_name : null,
            'processedBy' => $withdrawal->relationLoaded('processor') ? $withdrawal->processor?->name : null,
            'processedAt' => $withdrawal->processed_at?->toISOString(),
            'createdAt' => $withdrawal->created_at->toISOString(),
        ];
    }
}
