<?php

namespace App\Http\Requests\Checkout;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*' => [
                'integer',
                'distinct',
                Rule::exists('cart_items', 'id')->where('user_id', $this->user()->id),
            ],
            'recipient_name' => ['required', 'string', 'max:100'],
            'recipient_phone' => ['required', 'string', 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/'],
            'shipping_address' => ['required', 'string', 'min:10', 'max:500'],
            'courier' => ['required', Rule::in(array_keys(config('marketplace.couriers')))],
            'payment_method' => ['required', Rule::in(array_keys(config('marketplace.payment_methods')))],
            'notes' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'recipient_phone.regex' => 'Nomor HP penerima tidak valid (contoh: 081234567890).',
            'items.*.exists' => 'Ada item keranjang yang tidak ditemukan.',
        ];
    }
}
