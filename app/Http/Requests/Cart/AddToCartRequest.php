<?php

namespace App\Http\Requests\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AddToCartRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'product_variant_id' => ['nullable', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * Validasi yang butuh data produk: status, kepemilikan toko, varian, dan stok.
     *
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $product = Product::with('seller')->findOrFail($this->integer('product_id'));

            if (! $product->isApproved() || ! $product->seller->isApproved()) {
                $validator->errors()->add('product_id', 'Produk ini tidak tersedia.');

                return;
            }

            if ($product->seller->user_id === $this->user()->id) {
                $validator->errors()->add('product_id', 'Anda tidak dapat membeli produk dari toko sendiri.');

                return;
            }

            $variant = null;

            if ($product->variants()->where('is_active', true)->exists()) {
                $variant = ProductVariant::query()
                    ->where('product_id', $product->id)
                    ->where('is_active', true)
                    ->find($this->input('product_variant_id'));

                if (! $variant) {
                    $validator->errors()->add('product_variant_id', 'Pilih varian produk.');

                    return;
                }
            }

            $inCart = $this->user()->cartItems()
                ->where('product_id', $product->id)
                ->when(
                    $variant,
                    fn ($query) => $query->where('product_variant_id', $variant->id),
                    fn ($query) => $query->whereNull('product_variant_id'),
                )
                ->value('quantity') ?? 0;

            $stock = $variant?->stock ?? $product->stock;

            if ($inCart + $this->integer('quantity') > $stock) {
                $validator->errors()->add('quantity', "Stok tidak mencukupi (tersisa {$stock}, di keranjang {$inCart}).");
            }
        }];
    }
}
