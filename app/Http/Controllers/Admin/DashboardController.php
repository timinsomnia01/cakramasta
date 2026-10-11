<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;
use App\Models\Visit;
use App\Models\Withdrawal;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Statistik platform: pengunjung, pembeli terakhir, antrean verifikasi, dan akun aktif.
     */
    public function index(): Response
    {
        $completed = Order::query()->where('status', OrderStatus::Completed);

        return Inertia::render('admin/dashboard', [
            'visitors' => [
                'today' => Visit::uniqueVisitors(today()),
                'week' => Visit::uniqueVisitors(today()->subDays(6)),
                'month' => Visit::uniqueVisitors(today()->subDays(29)),
                'total' => Visit::uniqueVisitors(),
                'daily' => $this->dailyVisitors(14),
            ],
            'pending' => [
                'sellers' => Seller::query()->pending()->count(),
                'products' => Product::query()->pending()->count(),
                'payments' => Payment::query()->waitingVerification()->count(),
                'withdrawals' => Withdrawal::query()->where('status', WithdrawalStatus::Pending)->count(),
            ],
            'active' => [
                'buyers' => User::query()->role(UserRole::User)->where('is_active', true)->count(),
                'sellers' => Seller::query()->approved()->count(),
                'products' => Product::query()->approved()->count(),
                'verifikators' => User::query()->role(UserRole::Verifikator)->where('is_active', true)->count(),
            ],
            'sales' => [
                'ordersCompleted' => (clone $completed)->count(),
                'gmv' => (int) (clone $completed)->sum('total'),
                'platformFee' => (int) (clone $completed)->sum('platform_fee'),
                'tax' => (int) (clone $completed)->sum('tax_amount'),
            ],
            'latestBuyers' => Order::query()
                ->with(['buyer:id,name,email', 'seller:id,store_name'])
                ->whereNotIn('status', [OrderStatus::Cancelled, OrderStatus::PendingPayment])
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (Order $order) => [
                    'orderCode' => $order->code,
                    'buyerName' => $order->buyer?->name,
                    'buyerEmail' => $order->buyer?->email,
                    'storeName' => $order->seller?->store_name,
                    'total' => $order->total,
                    'status' => $order->status->value,
                    'statusLabel' => $order->status->label(),
                    'createdAt' => $order->created_at->toISOString(),
                    'href' => route('admin.orders.show', $order, absolute: false),
                ]),
        ]);
    }

    /**
     * Pengunjung unik per hari (sesi, atau IP bila sesi kosong) untuk grafik.
     *
     * @return list<array{date: string, visitors: int}>
     */
    private function dailyVisitors(int $days): array
    {
        $from = today()->subDays($days - 1);

        $counts = Visit::query()
            ->whereDate('visited_date', '>=', $from)
            ->select('visited_date', DB::raw('count(distinct coalesce(session_id, ip_address)) as total'))
            ->groupBy('visited_date')
            ->pluck('total', 'visited_date');

        return collect(range(0, $days - 1))
            ->map(function (int $offset) use ($from, $counts) {
                $date = $from->copy()->addDays($offset)->toDateString();
                $count = $counts->first(fn ($total, $key) => str_starts_with((string) $key, $date), 0);

                return ['date' => $date, 'visitors' => (int) $count];
            })
            ->all();
    }
}
