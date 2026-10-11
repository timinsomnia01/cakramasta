<?php

namespace App\Http\Requests\Account;

use App\Enums\SellerStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SellerRegistrationRequest extends FormRequest
{
    /**
     * Pendaftaran baru atau pengajuan ulang setelah ditolak (dokumen lama boleh dipertahankan).
     */
    public function authorize(): bool
    {
        $seller = $this->user()->seller;

        return $seller === null || $seller->status === SellerStatus::Rejected;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $seller = $this->user()->seller;
        $docRule = fn (bool $keepExisting) => [
            $keepExisting ? 'nullable' : 'required',
            'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('marketplace.max_document_kb'),
        ];

        return [
            'store_name' => ['required', 'string', 'min:3', 'max:60', Rule::unique('sellers', 'store_name')->ignore($seller?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'ktp_number' => ['required', 'digits:16', Rule::unique('sellers', 'ktp_number')->ignore($seller?->id)],
            'ktp_image' => $docRule($seller !== null),
            'selfie_image' => $docRule($seller !== null),
            'qris_image' => $docRule($seller !== null),
            'bank_name' => ['nullable', 'required_with:bank_account_number', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'required_with:bank_name', 'string', 'regex:/^[0-9]{6,20}$/'],
            'bank_account_name' => ['nullable', 'required_with:bank_name', 'string', 'max:100'],
            'agree' => ['accepted'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
            'ktp_number.digits' => 'Nomor KTP harus 16 digit.',
            'ktp_number.unique' => 'Nomor KTP ini sudah terdaftar.',
            'store_name.unique' => 'Nama toko sudah dipakai.',
            'agree.accepted' => 'Anda harus menyetujui syarat dan ketentuan penjual.',
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka (6-20 digit).',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'ktp_image' => 'foto KTP',
            'selfie_image' => 'foto selfie dengan KTP',
            'qris_image' => 'gambar QRIS',
        ];
    }
}
