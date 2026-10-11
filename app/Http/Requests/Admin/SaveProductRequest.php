<?php

namespace App\Http\Requests\Admin;

use App\Enums\ProductStatus;
use App\Http\Requests\Seller\SaveProductRequest as BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Admin boleh memilih toko pemilik produk dan menetapkan status langsung.
 */
class SaveProductRequest extends BaseRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return parent::rules() + [
            'seller_id' => [
                'required', 'integer',
                Rule::exists('sellers', 'id')->where('status', 'approved')->whereNull('deleted_at'),
            ],
        ];
    }

    /**
     * @return list<string>
     */
    protected function allowedStatuses(): array
    {
        return ProductStatus::values();
    }
}
