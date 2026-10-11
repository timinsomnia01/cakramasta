<?php

namespace App\Http\Controllers;

use App\Actions\Orders\PlaceOrder;
use App\Http\Requests\Checkout\PlaceOrderRequest;
use App\Support\Presenters\CartPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    /**
     * Ringkasan checkout. Gunakan ?items[]=ID untuk memilih item keranjang tertentu,
     * tanpa parameter berarti semua item yang bisa dibeli.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $ids = collect((array) $request->query('items', []))->filter(fn ($id) => is_numeric($id))->all();

        $items = $user->cartItems()
            ->with(CartPresenter::relations())
            ->when($ids !== [], fn ($query) => $query->whereIn('id', $ids))
            ->get()
            ->filter(fn ($item) => CartPresenter::isPurchasable($item))
            ->values();

        if ($items->isEmpty()) {
            Inertia::flash('toast', ['type' => 'warning', 'message' => __('Tidak ada item yang bisa dicheckout.')]);

            return to_route('cart.index');
        }

        return Inertia::render('checkout/create', [
            'groups' => CartPresenter::groups($items),
            'itemIds' => $items->modelKeys(),
            'couriers' => collect(config('marketplace.couriers'))
                ->map(fn (array $courier, string $key) => ['key' => $key, 'name' => $courier['name'], 'cost' => $courier['cost']])
                ->values(),
            'paymentMethods' => collect(config('marketplace.payment_methods'))
                ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
                ->values(),
            'defaults' => [
                'recipient_name' => $user->name,
                'recipient_phone' => $user->phone,
                'shipping_address' => $user->address,
            ],
            'feeRate' => config('marketplace.platform_fee_rate'),
        ]);
    }

    /**
     * Buat order (satu per penjual) dari item keranjang, lalu arahkan ke halaman pembayaran.
     */
    public function store(PlaceOrderRequest $request, PlaceOrder $placeOrder): RedirectResponse
    {
        $data = $request->validated();

        $orders = $placeOrder->handle($request->user(), $data['items'], $data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pesanan dibuat. Silakan lakukan pembayaran.')]);

        return $orders->count() === 1
            ? to_route('account.orders.payment.show', ['order' => $orders->first()])
            : to_route('account.orders.index');
    }
}
