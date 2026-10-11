<?php

namespace App\Http\Controllers\Account;

use App\Actions\Payments\SubmitPaymentProof;
use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\UploadPaymentProofRequest;
use App\Models\Order;
use App\Support\Presenters\OrderPresenter;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Halaman pembayaran: nominal, batas waktu, QRIS / rekening UM-Mart, dan unggah bukti.
     */
    public function show(Order $order): Response|RedirectResponse
    {
        Gate::authorize('buyerAction', $order);

        if (! in_array($order->status, [OrderStatus::PendingPayment, OrderStatus::WaitingVerification], true)) {
            return to_route('account.orders.show', $order);
        }

        $order->load(['items', 'latestPayment']);

        return Inertia::render('account/orders/payment', [
            'order' => OrderPresenter::detail($order),
            'destination' => [
                'qrisImage' => ProductPresenter::imageUrl(config('marketplace.qris_image')),
                'bank' => config('marketplace.bank_account'),
            ],
            'canUpload' => $order->status === OrderStatus::PendingPayment && $order->latestPayment && ! $order->latestPayment->isExpired(),
        ]);
    }

    public function store(UploadPaymentProofRequest $request, Order $order, SubmitPaymentProof $submit): RedirectResponse
    {
        Gate::authorize('buyerAction', $order);

        $submit->handle($order, $request->user(), $request->file('proof'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Bukti pembayaran terkirim. Menunggu verifikasi.')]);

        return to_route('account.orders.show', $order);
    }
}
