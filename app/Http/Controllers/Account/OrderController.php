<?php

namespace App\Http\Controllers\Account;

use App\Actions\Orders\CancelOrder;
use App\Actions\Orders\CompleteOrder;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\CancelOrderRequest;
use App\Models\Order;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    /**
     * Riwayat pesanan pembeli, bisa difilter berdasarkan status dan kode / nama produk.
     */
    public function index(Request $request): Response
    {
        $status = OrderStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q', ''));

        $orders = $request->user()->orders()
            ->with(['items', 'seller:id,store_name'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('code', 'like', "%{$search}%")
                    ->orWhereHas('items', fn ($query) => $query->where('product_name', 'like', "%{$search}%"));
            }))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Order $order) => OrderPresenter::row($order, 'account.orders.show'));

        return Inertia::render('account/orders/index', [
            'orders' => $orders,
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(OrderStatus::cases())->map(fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function show(Order $order): Response
    {
        Gate::authorize('buyerAction', $order);

        $order->load(['items.review', 'seller', 'buyer', 'latestPayment', 'statusLogs.changer']);

        return Inertia::render('account/orders/show', [
            'order' => OrderPresenter::detail($order),
            'can' => [
                'pay' => $order->status === OrderStatus::PendingPayment,
                'cancel' => in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::WaitingVerification], true),
                'complete' => $order->status === OrderStatus::Shipped,
                'review' => $order->status === OrderStatus::Completed,
            ],
        ]);
    }

    public function cancel(CancelOrderRequest $request, Order $order, CancelOrder $cancelOrder): RedirectResponse
    {
        Gate::authorize('buyerAction', $order);

        $cancelOrder->handle($order, $request->user(), $request->validated('reason') ?: 'Dibatalkan oleh pembeli.');

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pesanan dibatalkan.')]);

        return back();
    }

    public function complete(Request $request, Order $order, CompleteOrder $completeOrder): RedirectResponse
    {
        Gate::authorize('buyerAction', $order);

        $completeOrder->handle($order, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Terima kasih! Pesanan ditandai selesai.')]);

        return back();
    }
}
