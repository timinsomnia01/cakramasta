<?php

namespace App\Http\Requests\Seller;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RequestWithdrawalRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'amount' => ['required', 'integer', 'min:'.config('marketplace.min_withdrawal')],
            'method' => ['required', Rule::in(['qris', 'bank_transfer'])],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * Transfer bank mensyaratkan rekening sudah diisi di profil toko.
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            $seller = $this->user()->seller;

            if ($this->input('method') === 'bank_transfer' && ! ($seller?->bank_account_number && $seller?->bank_name)) {
                $validator->errors()->add('method', 'Lengkapi data rekening bank di pengaturan toko terlebih dahulu.');
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['amount' => 'nominal pencairan'];
    }
}
