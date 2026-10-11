<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ShipOrderRequest;
use App\Models\Order;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Pesanan masuk. Pesanan yang belum dibayar tidak ditampilkan karena belum perlu ditindak penjual.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user()->seller;
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q', ''));

        $hidden = [OrderStatus::PendingPayment, OrderStatus::WaitingVerification];

        return Inertia::render('seller/orders/index', [
            'orders' => $seller->orders()
                ->whereNotIn('status', $hidden)
                ->with(['items', 'buyer:id,name'])
                ->when($status && ! in_array($status, $hidden, true), fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhere('recipient_name', 'like', "%{$search}%")))
                ->latest()
                ->paginate(10)
                ->withQueryString()
                ->through(fn (Order $order) => OrderPresenter::row($order, 'seller.orders.show')),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(OrderStatus::cases())
                ->reject(fn (OrderStatus $s) => in_array($s, $hidden, true))
                ->map(fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label()])
                ->values(),
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorizeOrder($order);

        $order->load(['items', 'buyer', 'seller', 'latestPayment', 'statusLogs.changer']);

        return Inertia::render('seller/orders/show', [
            'order' => OrderPresenter::detail($order),
            'can' => [
                'process' => $order->status === OrderStatus::Paid,
                'ship' => $order->status === OrderStatus::Processing,
            ],
            'requiresTracking' => (bool) (config("marketplace.couriers.{$order->courier}.tracking") ?? true),
        ]);
    }

    /**
     * Terima dan mulai memproses pesanan yang sudah dibayar.
     */
    public function process(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        $this->ensureStatus($order, OrderStatus::Paid, 'Hanya pesanan yang sudah dibayar yang dapat diproses.');

        $order->transitionTo(OrderStatus::Processing, $request->user(), 'Pesanan sedang disiapkan penjual.');

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pesanan diproses.')]);

        return back();
    }

    public function ship(ShipOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        $this->ensureStatus($order, OrderStatus::Processing, 'Pesanan harus diproses terlebih dahulu sebelum dikirim.');

        $tracking = $request->validated('tracking_number');
        $order->forceFill(['tracking_number' => $tracking]);
        $order->transitionTo(
            OrderStatus::Shipped,
            $request->user(),
            $tracking ? "Dikirim dengan nomor resi {$tracking}." : 'Pesanan dikirim / diserahkan ke pembeli.',
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pesanan ditandai dikirim.')]);

        return back();
    }

    private function authorizeOrder(Order $order): void
    {
        Gate::authorize('sellerAction', $order);
    }

    private function ensureStatus(Order $order, OrderStatus $expected, string $message): void
    {
        if ($order->status !== $expected) {
            throw ValidationException::withMessages(['order' => $message]);
        }
    }
}
