<?php

namespace App\Http\Requests\Seller;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'description' => ['nullable', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'address' => ['required', 'string', 'min:10', 'max:500'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('marketplace.max_image_kb')],
            'qris_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('marketplace.max_document_kb')],
            'bank_name' => ['nullable', 'required_with:bank_account_number', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'required_with:bank_name', 'string', 'regex:/^[0-9]{6,20}$/'],
            'bank_account_name' => ['nullable', 'required_with:bank_name', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'phone.regex' => 'Nomor HP tidak valid (contoh: 081234567890).',
            'bank_account_number.regex' => 'Nomor rekening hanya boleh berisi angka (6-20 digit).',
        ];
    }
}
