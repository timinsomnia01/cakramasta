<?php

namespace App\Actions\Sellers;

use App\Enums\SellerStatus;
use App\Models\Seller;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RegisterSeller
{
    /**
     * Daftarkan (atau ajukan ulang) toko. Dokumen sensitif (KTP, selfie, QRIS) disimpan di disk privat.
     * Pengajuan ulang hanya berlaku untuk pendaftaran yang ditolak; berkas lama dipertahankan bila tidak diganti.
     *
     * @param  array<string, mixed>  $data
     * @param  array{ktp_image?: UploadedFile|null, selfie_image?: UploadedFile|null, qris_image?: UploadedFile|null}  $files
     */
    public function handle(User $user, ?Seller $seller, array $data, array $files): Seller
    {
        $seller ??= new Seller(['user_id' => $user->id]);

        $seller->fill([
            'store_name' => $data['store_name'],
            'description' => $data['description'] ?? null,
            'phone' => $data['phone'],
            'address' => $data['address'],
            'ktp_number' => $data['ktp_number'],
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_account_name' => $data['bank_account_name'] ?? null,
        ]);

        foreach (['ktp_image' => 'ktp', 'selfie_image' => 'selfie', 'qris_image' => 'qris'] as $column => $directory) {
            if (($file = $files[$column] ?? null) instanceof UploadedFile) {
                if ($seller->exists && $seller->getOriginal($column)) {
                    Storage::disk('local')->delete($seller->getOriginal($column));
                }

                $seller->{$column} = $file->store("sellers/{$directory}", 'local');
            }
        }

        $seller->forceFill([
            'status' => SellerStatus::Pending,
            'rejection_reason' => null,
            'verified_by' => null,
            'verified_at' => null,
        ])->save();

        return $seller;
    }
}
