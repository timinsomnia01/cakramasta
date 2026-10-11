<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CancelOrder
{
    /**
     * Batalkan order yang belum dibayar / menunggu verifikasi pembayaran, lalu kembalikan stoknya.
     *
     * @throws ValidationException
     */
    public function handle(Order $order, ?User $by, string $reason): Order
    {
        return DB::transaction(function () use ($order, $by, $reason) {
            $order = Order::query()->lockForUpdate()->findOrFail($order->id);

            if (! in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::WaitingVerification], true)) {
                throw ValidationException::withMessages(['order' => 'Pesanan ini sudah diproses dan tidak dapat dibatalkan.']);
            }

            foreach ($order->items as $item) {
                if ($item->product_variant_id) {
                    ProductVariant::query()->whereKey($item->product_variant_id)->increment('stock', $item->quantity);
                }

                if ($item->product_id) {
                    Product::withTrashed()->whereKey($item->product_id)->increment('stock', $item->quantity);
                }
            }

            $order->payments()
                ->whereIn('status', [PaymentStatus::Pending, PaymentStatus::WaitingVerification])
                ->update(['status' => PaymentStatus::Expired]);

            $order->forceFill(['cancel_reason' => $reason]);
            $order->transitionTo(OrderStatus::Cancelled, $by, $reason);

            return $order;
        });
    }
}
