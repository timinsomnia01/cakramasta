<?php

namespace App\Http\Requests\Verification;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class MarkWithdrawalPaidRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'transfer_proof' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('marketplace.max_image_kb')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['transfer_proof' => 'bukti transfer'];
    }
}
