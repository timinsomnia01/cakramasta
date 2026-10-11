<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Ringkasan akun pembeli: jumlah pesanan per status, pesanan terbaru, dan status toko.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $counts = $user->orders()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $recent = $user->orders()->with(['items', 'seller:id,store_name'])->latest()->limit(5)->get();

        return Inertia::render('account/dashboard', [
            'stats' => [
                'unpaid' => (int) ($counts[OrderStatus::PendingPayment->value] ?? 0) + (int) ($counts[OrderStatus::WaitingVerification->value] ?? 0),
                'inProgress' => (int) ($counts[OrderStatus::Paid->value] ?? 0) + (int) ($counts[OrderStatus::Processing->value] ?? 0),
                'shipped' => (int) ($counts[OrderStatus::Shipped->value] ?? 0),
                'completed' => (int) ($counts[OrderStatus::Completed->value] ?? 0),
                'saved' => $user->savedProducts()->count(),
            ],
            'recentOrders' => $recent->map(fn ($order) => OrderPresenter::row($order, 'account.orders.show')),
            'seller' => $user->seller ? [
                'status' => $user->seller->status->value,
                'statusLabel' => $user->seller->status->label(),
                'storeName' => $user->seller->store_name,
            ] : null,
        ]);
    }
}
