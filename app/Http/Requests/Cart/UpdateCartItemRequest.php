<?php

namespace App\Http\Requests\Cart;

use App\Models\CartItem;
use App\Support\Presenters\CartPresenter;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateCartItemRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            /** @var CartItem $item */
            $item = $this->route('cartItem');
            $item->loadMissing(CartPresenter::relations());

            $stock = CartPresenter::stock($item);

            if (! $validator->errors()->has('quantity') && $this->integer('quantity') > $stock) {
                $validator->errors()->add('quantity', "Stok tidak mencukupi (tersisa {$stock}).");
            }
        }];
    }
}
