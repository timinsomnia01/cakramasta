<?php

namespace App\Support\Presenters;

use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Payment;

/**
 * Mengubah model order menjadi array siap kirim ke komponen Inertia.
 */
class OrderPresenter
{
    /**
     * Baris order untuk daftar. Relasi items (dan buyer / seller bila perlu) harus sudah dimuat.
     *
     * @param  string  $route  Nama route halaman detail, menerima order (kunci route = code).
     * @return array<string, mixed>
     */
    public static function row(Order $order, string $route): array
    {
        return [
            'code' => $order->code,
            'status' => $order->status->value,
            'statusLabel' => $order->status->label(),
            'total' => $order->total,
            'sellerIncome' => $order->seller_income,
            'createdAt' => $order->created_at->toISOString(),
            'buyerName' => $order->relationLoaded('buyer') ? $order->buyer?->name : null,
            'storeName' => $order->relationLoaded('seller') ? $order->seller?->store_name : null,
            'itemsCount' => $order->items->count(),
            'items' => $order->items->take(3)->map(fn ($item) => [
                'name' => $item->product_name,
                'variant' => $item->variant_name,
                'quantity' => $item->quantity,
                'image' => ProductPresenter::imageUrl($item->product_image),
            ])->values(),
            'href' => route($route, $order, absolute: false),
        ];
    }

    /**
     * Detail order lengkap. Relasi items, buyer, seller, latestPayment, dan statusLogs.changer
     * harus sudah dimuat.
     *
     * @return array<string, mixed>
     */
    public static function detail(Order $order): array
    {
        return [
            'code' => $order->code,
            'status' => $order->status->value,
            'statusLabel' => $order->status->label(),
            'subtotal' => $order->subtotal,
            'shippingCost' => $order->shipping_cost,
            'platformFee' => $order->platform_fee,
            'taxAmount' => $order->tax_amount,
            'total' => $order->total,
            'sellerIncome' => $order->seller_income,
            'recipient' => [
                'name' => $order->recipient_name,
                'phone' => $order->recipient_phone,
                'address' => $order->shipping_address,
            ],
            'courier' => $order->courier,
            'trackingNumber' => $order->tracking_number,
            'notes' => $order->notes,
            'cancelReason' => $order->cancel_reason,
            'createdAt' => $order->created_at->toISOString(),
            'paidAt' => $order->paid_at?->toISOString(),
            'shippedAt' => $order->shipped_at?->toISOString(),
            'completedAt' => $order->completed_at?->toISOString(),
            'cancelledAt' => $order->cancelled_at?->toISOString(),
            'buyer' => $order->buyer ? [
                'name' => $order->buyer->name,
                'email' => $order->buyer->email,
                'phone' => $order->buyer->phone,
            ] : null,
            'seller' => $order->seller ? [
                'storeName' => $order->seller->store_name,
                'slug' => $order->seller->slug,
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'productName' => $item->product_name,
                'variantName' => $item->variant_name,
                'image' => ProductPresenter::imageUrl($item->product_image),
                'price' => $item->price,
                'quantity' => $item->quantity,
                'subtotal' => $item->subtotal,
                'reviewed' => $item->relationLoaded('review') ? $item->review !== null : null,
            ])->values(),
            'logs' => $order->statusLogs->map(fn (OrderStatusLog $log) => [
                'status' => $log->status->value,
                'statusLabel' => $log->status->label(),
                'note' => $log->note,
                'by' => $log->changer?->name,
                'createdAt' => $log->created_at->toISOString(),
            ])->values(),
            'payment' => $order->latestPayment ? self::payment($order->latestPayment) : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function payment(Payment $payment): array
    {
        return [
            'id' => $payment->id,
            'method' => $payment->method,
            'methodLabel' => config("marketplace.payment_methods.{$payment->method}", $payment->method),
            'status' => $payment->status->value,
            'statusLabel' => $payment->status->label(),
            'amount' => $payment->amount,
            'expiresAt' => $payment->expires_at?->toISOString(),
            'isExpired' => $payment->isExpired(),
            'rejectionReason' => $payment->rejection_reason,
            'hasProof' => $payment->proof_image !== null,
            'proofUrl' => $payment->proof_image ? route('files.payment-proof', $payment, absolute: false) : null,
        ];
    }
}
