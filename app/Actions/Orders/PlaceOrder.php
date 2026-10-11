<?php

namespace App\Actions\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    /**
     * Ubah item keranjang menjadi order. Item dipecah per penjual (satu order = satu penjual),
     * stok dikurangi, dan tagihan pembayaran dibuat. Semuanya dalam satu transaksi.
     *
     * @param  list<int>  $cartItemIds
     * @param  array{recipient_name: string, recipient_phone: string, shipping_address: string, courier: string, payment_method: string, notes?: string|null}  $data
     * @return Collection<int, Order>
     *
     * @throws ValidationException
     */
    public function handle(User $user, array $cartItemIds, array $data): Collection
    {
        return DB::transaction(function () use ($user, $cartItemIds, $data) {
            $cartItems = $user->cartItems()
                ->whereIn('id', $cartItemIds)
                ->with(['product.seller', 'product.primaryImage', 'variant'])
                ->get();

            if ($cartItems->isEmpty()) {
                throw ValidationException::withMessages(['items' => 'Keranjang belanja kosong.']);
            }

            $orders = $cartItems
                ->groupBy(fn (CartItem $item) => $item->product->seller_id)
                ->map(fn (Collection $items) => $this->placeForSeller($user, $items, $data))
                ->values();

            $user->cartItems()->whereIn('id', $cartItems->modelKeys())->delete();

            return $orders;
        });
    }

    /**
     * @param  Collection<int, CartItem>  $items  Semua item milik satu penjual.
     * @param  array<string, mixed>  $data
     */
    private function placeForSeller(User $user, Collection $items, array $data): Order
    {
        $seller = $items->first()->product->seller;

        if ($seller->user_id === $user->id) {
            throw ValidationException::withMessages(['items' => 'Anda tidak dapat membeli produk dari toko sendiri.']);
        }

        if (! $seller->isApproved()) {
            throw ValidationException::withMessages(['items' => "Toko {$seller->store_name} sedang tidak aktif."]);
        }

        $lines = $items->map(fn (CartItem $item) => $this->reserveStock($item));
        $subtotal = (int) $lines->sum('subtotal');

        $courier = config("marketplace.couriers.{$data['courier']}");
        $shipping = (int) $courier['cost'];
        $platformFee = (int) round($subtotal * config('marketplace.platform_fee_rate'));
        $tax = (int) round($subtotal * config('marketplace.tax_rate'));

        $order = Order::create([
            'user_id' => $user->id,
            'seller_id' => $seller->id,
            'status' => OrderStatus::PendingPayment,
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'platform_fee' => $platformFee,
            'tax_amount' => $tax,
            'total' => $subtotal + $shipping,
            'seller_income' => $subtotal - $platformFee - $tax,
            'recipient_name' => $data['recipient_name'],
            'recipient_phone' => $data['recipient_phone'],
            'shipping_address' => $data['shipping_address'],
            'courier' => $courier['name'],
            'notes' => $data['notes'] ?? null,
        ]);

        foreach ($lines as $line) {
            $order->items()->create($line);
        }

        $order->payments()->create([
            'method' => $data['payment_method'],
            'amount' => $order->total,
            'status' => PaymentStatus::Pending,
            'expires_at' => now()->addHours(config('marketplace.payment_expiry_hours')),
        ]);

        return $order;
    }

    /**
     * Kunci baris produk/varian, periksa ketersediaan, kurangi stok, lalu kembalikan data item order
     * (nama dan harga disalin agar histori tidak berubah bila produk diedit).
     *
     * @return array<string, mixed>
     */
    private function reserveStock(CartItem $item): array
    {
        $product = Product::query()->lockForUpdate()->find($item->product_id);
        $variant = $item->product_variant_id
            ? ProductVariant::query()->lockForUpdate()->find($item->product_variant_id)
            : null;

        $name = $item->product->name;

        if (! $product || ! $product->isApproved() || ($item->product_variant_id && (! $variant || ! $variant->is_active))) {
            throw ValidationException::withMessages(['items' => "{$name} sudah tidak tersedia."]);
        }

        $stock = $variant?->stock ?? $product->stock;

        if ($stock < $item->quantity) {
            throw ValidationException::withMessages(['items' => "Stok {$name} tidak mencukupi (tersisa {$stock})."]);
        }

        $variant?->decrement('stock', $item->quantity);
        $product->decrement('stock', $item->quantity);

        $price = $variant?->price ?? $product->price;

        return [
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'product_name' => $product->name,
            'variant_name' => $variant?->name,
            'product_image' => $item->product->primaryImage?->path,
            'price' => $price,
            'quantity' => $item->quantity,
            'subtotal' => $price * $item->quantity,
        ];
    }
}
