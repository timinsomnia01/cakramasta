<?php

namespace App\Http\Controllers\Verification;

use App\Actions\Payments\ReviewPayment;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Verification\RejectionRequest;
use App\Models\Payment;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PaymentController extends Controller
{
    /**
     * Pembayaran / pembelian yang perlu diverifikasi (bukti transfer sudah diunggah pembeli).
     */
    public function index(Request $request): Response
    {
        $status = PaymentStatus::tryFrom((string) $request->query('status', 'waiting_verification'));
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('verification/payments/index', [
            'payments' => Payment::query()
                ->with('order.buyer:id,name')
                ->whereNotNull('proof_image')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->whereHas('order', fn ($query) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhereHas('buyer', fn ($query) => $query->where('name', 'like', "%{$search}%"))))
                ->oldest('paid_at')
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Payment $payment) => OrderPresenter::payment($payment) + [
                    'orderCode' => $payment->order->code,
                    'buyerName' => $payment->order->buyer?->name,
                    'paidAt' => $payment->paid_at?->toISOString(),
                ]),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(PaymentStatus::cases())->map(fn (PaymentStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function show(Payment $payment): Response
    {
        $payment->load(['order.items', 'order.buyer', 'order.seller', 'order.latestPayment', 'order.statusLogs.changer', 'verifier:id,name']);

        return Inertia::render('verification/payments/show', [
            'payment' => OrderPresenter::payment($payment) + ['verifiedBy' => $payment->verifier?->name],
            'order' => OrderPresenter::detail($payment->order),
            'canReview' => $payment->status === PaymentStatus::WaitingVerification,
        ]);
    }

    public function approve(Request $request, Payment $payment, ReviewPayment $review): RedirectResponse
    {
        $review->approve($payment, $request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pembayaran diverifikasi. Pesanan diteruskan ke penjual.')]);

        return to_route('verification.payments.index');
    }

    public function reject(RejectionRequest $request, Payment $payment, ReviewPayment $review): RedirectResponse
    {
        $review->reject($payment, $request->user(), $request->validated('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pembayaran ditolak. Pembeli dapat mengunggah ulang bukti.')]);

        return to_route('verification.payments.index');
    }
}
