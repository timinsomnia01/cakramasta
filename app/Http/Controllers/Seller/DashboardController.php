<?php

namespace App\Http\Controllers\Seller;

use App\Enums\OrderStatus;
use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Ringkasan toko: saldo, pesanan yang perlu ditindak, status produk, dan pesanan terbaru.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user()->seller;

        $orderCounts = $seller->orders()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $productCounts = $seller->products()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return Inertia::render('seller/dashboard', [
            'store' => ['name' => $seller->store_name, 'slug' => $seller->slug],
            'stats' => [
                'balance' => $seller->availableBalance(),
                'totalIncome' => $seller->totalIncome(),
                'toProcess' => (int) ($orderCounts[OrderStatus::Paid->value] ?? 0),
                'processing' => (int) ($orderCounts[OrderStatus::Processing->value] ?? 0),
                'shipped' => (int) ($orderCounts[OrderStatus::Shipped->value] ?? 0),
                'completed' => (int) ($orderCounts[OrderStatus::Completed->value] ?? 0),
                'products' => [
                    'approved' => (int) ($productCounts[ProductStatus::Approved->value] ?? 0),
                    'pending' => (int) ($productCounts[ProductStatus::Pending->value] ?? 0),
                    'rejected' => (int) ($productCounts[ProductStatus::Rejected->value] ?? 0),
                    'draft' => (int) ($productCounts[ProductStatus::Draft->value] ?? 0),
                ],
            ],
            'lowStock' => $seller->products()->approved()->where('stock', '<=', 3)->orderBy('stock')->limit(5)->get(['id', 'name', 'stock']),
            'recentOrders' => $seller->orders()->with(['items', 'buyer:id,name'])->latest()->limit(5)->get()
                ->map(fn ($order) => OrderPresenter::row($order, 'seller.orders.show')),
        ]);
    }
}
