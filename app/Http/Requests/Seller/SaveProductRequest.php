<?php

namespace App\Http\Requests\Seller;

use App\Models\Product;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveProductRequest extends FormRequest
{
    /**
     * Otorisasi ditangani di controller (ProductPolicy) dan middleware.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $product = $this->product();
        $hasVariants = filled($this->input('variants'));
        $maxImages = (int) config('marketplace.max_product_images');

        return [
            'category_id' => [
                'required', 'integer',
                Rule::exists('categories', 'id')->where(fn ($query) => $query->whereNotNull('parent_id')->where('is_active', true)->whereNull('deleted_at')),
            ],
            'name' => ['required', 'string', 'min:3', 'max:150'],
            'sku' => ['nullable', 'string', 'max:50'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'price' => [Rule::requiredIf(! $hasVariants), 'nullable', 'integer', 'min:100', 'max:1000000000'],
            'stock' => [Rule::requiredIf(! $hasVariants), 'nullable', 'integer', 'min:0', 'max:100000'],
            'weight' => ['required', 'integer', 'min:1', 'max:100000'],
            'condition' => ['required', Rule::in(['new', 'used'])],
            'status' => ['nullable', Rule::in($this->allowedStatuses())],

            'images' => ['nullable', 'array', 'max:'.$maxImages],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:'.config('marketplace.max_image_kb')],
            'keep_image_ids' => ['nullable', 'array'],
            'keep_image_ids.*' => ['integer', Rule::exists('product_images', 'id')->where('product_id', $product?->id ?? 0)],
            'primary_image_id' => ['nullable', 'integer'],

            'variants' => ['nullable', 'array', 'max:30'],
            'variants.*.id' => ['nullable', 'integer', Rule::exists('product_variants', 'id')->where('product_id', $product?->id ?? 0)],
            'variants.*.name' => ['required', 'string', 'max:100', 'distinct'],
            'variants.*.sku' => ['nullable', 'string', 'max:50'],
            'variants.*.price' => ['required', 'integer', 'min:100', 'max:1000000000'],
            'variants.*.stock' => ['required', 'integer', 'min:0', 'max:100000'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * Total gambar (yang dipertahankan + baru) tidak boleh melebihi batas, dan produk yang diajukan
     * wajib memiliki minimal satu gambar.
     */
    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $product = $this->product();
            $keptCount = $product === null
                ? 0
                : ($this->has('keep_image_ids') ? count((array) $this->input('keep_image_ids')) : $product->images()->count());
            $total = $keptCount + count($this->file('images', []));

            if ($total > (int) config('marketplace.max_product_images')) {
                $validator->errors()->add('images', 'Maksimal '.config('marketplace.max_product_images').' gambar per produk.');
            }

            if ($total === 0 && $this->input('status') !== 'draft') {
                $validator->errors()->add('images', 'Unggah minimal satu gambar produk.');
            }
        }];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'category_id.exists' => 'Pilih sub kategori yang valid.',
            'variants.*.name.distinct' => 'Nama varian tidak boleh sama.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'category_id' => 'kategori',
            'name' => 'nama produk',
            'description' => 'deskripsi',
            'price' => 'harga',
            'stock' => 'stok',
            'weight' => 'berat',
            'condition' => 'kondisi',
            'images' => 'gambar',
            'images.*' => 'gambar',
            'variants.*.name' => 'nama varian',
            'variants.*.price' => 'harga varian',
            'variants.*.stock' => 'stok varian',
        ];
    }

    /**
     * Produk yang sedang diubah (null saat membuat baru).
     */
    protected function product(): ?Product
    {
        $product = $this->route('product');

        return $product instanceof Product ? $product : null;
    }

    /**
     * Penjual hanya boleh memilih draft; status lain ditentukan sistem / verifikator.
     *
     * @return list<string>
     */
    protected function allowedStatuses(): array
    {
        return ['draft'];
    }
}
