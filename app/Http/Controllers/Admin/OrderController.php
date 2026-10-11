<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Seller;
use App\Support\Exports\XlsxWriter;
use App\Support\Presenters\OrderPresenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class OrderController extends Controller
{
    /**
     * Log pembelian seluruh platform, dengan pencarian dan filter status, toko, serta rentang tanggal.
     */
    public function index(Request $request): Response
    {
        $filters = $this->filters($request);

        return Inertia::render('admin/orders/index', [
            'orders' => $this->query($filters)
                ->with(['items', 'buyer:id,name', 'seller:id,store_name'])
                ->latest()
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Order $order) => OrderPresenter::row($order, 'admin.orders.show')),
            'filters' => $filters,
            'statuses' => collect(OrderStatus::cases())->map(fn (OrderStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
            'sellers' => Seller::query()->orderBy('store_name')->get(['id', 'store_name'])
                ->map(fn (Seller $seller) => ['id' => $seller->id, 'name' => $seller->store_name]),
            'exportUrl' => route('admin.orders.export', array_filter($filters, fn ($value) => $value !== null && $value !== ''), absolute: false),
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load(['items', 'buyer', 'seller', 'latestPayment', 'statusLogs.changer']);

        return Inertia::render('admin/orders/show', [
            'order' => OrderPresenter::detail($order),
            'canDelete' => $order->status === OrderStatus::Cancelled,
        ]);
    }

    /**
     * Ekspor log pembelian (sesuai filter yang aktif) ke file Excel (.xlsx).
     */
    public function export(Request $request): BinaryFileResponse
    {
        $query = $this->query($this->filters($request))
            ->with(['items', 'buyer:id,name,email', 'seller:id,store_name', 'latestPayment']);

        $rows = (function () use ($query) {
            foreach ($query->lazyByIdDesc(500) as $order) {
                yield [
                    $order->code,
                    $order->created_at->timezone(config('app.timezone'))->format('d/m/Y H:i'),
                    $order->buyer?->name,
                    $order->buyer?->email,
                    $order->seller?->store_name,
                    $order->items->map(fn ($item) => "{$item->product_name}".($item->variant_name ? " ({$item->variant_name})" : '')." x{$item->quantity}")->implode('; '),
                    $order->status->label(),
                    $order->subtotal,
                    $order->shipping_cost,
                    $order->platform_fee,
                    $order->tax_amount,
                    $order->total,
                    $order->seller_income,
                    config("marketplace.couriers.{$order->courier}.name", $order->courier),
                    $order->tracking_number,
                    $order->latestPayment ? config("marketplace.payment_methods.{$order->latestPayment->method}", $order->latestPayment->method) : null,
                    $order->paid_at?->format('d/m/Y H:i'),
                ];
            }
        })();

        $path = XlsxWriter::make('Log Pembelian', [
            'Kode Order', 'Tanggal', 'Pembeli', 'Email Pembeli', 'Toko', 'Produk', 'Status',
            'Subtotal', 'Ongkir', 'Biaya Platform', 'Pajak', 'Total', 'Pendapatan Penjual',
            'Kurir', 'No. Resi', 'Metode Bayar', 'Tanggal Bayar',
        ], $rows, [7, 8, 9, 10, 11, 12]);

        return response()->download($path, 'log-pembelian-um-mart-'.now()->format('Ymd-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend();
    }

    /**
     * Hapus order. Hanya order berstatus "dibatalkan" (tanpa transaksi uang) yang boleh dihapus;
     * order lain adalah catatan keuangan dan harus tetap ada.
     */
    public function destroy(Order $order): RedirectResponse
    {
        if ($order->status !== OrderStatus::Cancelled) {
            throw ValidationException::withMessages(['order' => 'Hanya pesanan yang dibatalkan yang dapat dihapus.']);
        }

        $order->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pesanan dihapus.')]);

        return to_route('admin.orders.index');
    }

    /**
     * @return array{search: string, status: ?string, seller_id: ?int, from: ?string, to: ?string}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:'.implode(',', OrderStatus::values())],
            'seller_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        return [
            'search' => trim((string) ($validated['search'] ?? '')),
            'status' => $validated['status'] ?? null,
            'seller_id' => isset($validated['seller_id']) ? (int) $validated['seller_id'] : null,
            'from' => $validated['from'] ?? null,
            'to' => $validated['to'] ?? null,
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Builder<Order>
     */
    private function query(array $filters): Builder
    {
        return Order::query()->filter($filters);
    }
}
