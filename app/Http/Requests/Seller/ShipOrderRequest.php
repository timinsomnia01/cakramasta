<?php

namespace App\Http\Requests\Seller;

use App\Models\Order;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class ShipOrderRequest extends FormRequest
{
    /**
     * Nomor resi wajib kecuali kurir tidak memakai resi (COD / ambil di tempat).
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $order = $this->route('order');
        $needsTracking = $order instanceof Order && (config("marketplace.couriers.{$order->courier}.tracking") ?? true);

        return [
            'tracking_number' => [$needsTracking ? 'required' : 'nullable', 'string', 'max:60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['tracking_number' => 'nomor resi'];
    }
}
