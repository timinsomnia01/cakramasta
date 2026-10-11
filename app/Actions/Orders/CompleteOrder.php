<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CompleteOrder
{
    /**
     * Selesaikan pesanan yang sudah dikirim (konfirmasi pembeli atau otomatis oleh sistem),
     * lalu tambahkan jumlah terjual produk.
     *
     * @throws ValidationException
     */
    public function handle(Order $order, ?User $by = null, ?string $note = null): Order
    {
        return DB::transaction(function () use ($order, $by, $note) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if ($order->status !== OrderStatus::Shipped) {
                throw ValidationException::withMessages(['order' => 'Hanya pesanan yang sedang dikirim yang dapat diselesaikan.']);
            }

            $order->transitionTo(OrderStatus::Completed, $by, $note ?? 'Pesanan diterima pembeli.');

            foreach ($order->items()->whereNotNull('product_id')->get() as $item) {
                Product::withTrashed()->whereKey($item->product_id)->increment('sold_count', $item->quantity);
            }

            return $order;
        });
    }
}
