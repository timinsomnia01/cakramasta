<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Enums\SellerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendSellerRequest;
use App\Models\Seller;
use App\Support\Presenters\SellerPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class SellerController extends Controller
{
    public function index(Request $request): Response
    {
        $status = SellerStatus::tryFrom((string) $request->query('status'));
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('admin/sellers/index', [
            'sellers' => Seller::query()
                ->with('user:id,name,email')
                ->withCount(['products', 'orders'])
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('store_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
                ->latest()
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Seller $seller) => SellerPresenter::row($seller)),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(SellerStatus::cases())->map(fn (SellerStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function show(Seller $seller): Response
    {
        $seller->load(['user:id,name,email,phone', 'verifier:id,name']);
        $seller->loadCount(['products', 'orders']);

        return Inertia::render('admin/sellers/show', [
            'seller' => SellerPresenter::detail($seller) + [
                'balance' => $seller->availableBalance(),
                'totalIncome' => $seller->totalIncome(),
                'activeOrders' => $seller->orders()->whereIn('status', [OrderStatus::Paid, OrderStatus::Processing, OrderStatus::Shipped])->count(),
            ],
            'can' => [
                'suspend' => $seller->status === SellerStatus::Approved,
                'reactivate' => $seller->status === SellerStatus::Suspended,
            ],
        ]);
    }

    /**
     * Nonaktifkan toko: produknya hilang dari katalog dan toko tidak bisa masuk area penjual.
     */
    public function suspend(SuspendSellerRequest $request, Seller $seller): RedirectResponse
    {
        if ($seller->status !== SellerStatus::Approved) {
            throw ValidationException::withMessages(['seller' => 'Hanya toko yang aktif yang dapat dinonaktifkan.']);
        }

        $seller->forceFill([
            'status' => SellerStatus::Suspended,
            'rejection_reason' => $request->validated('reason'),
        ])->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Toko dinonaktifkan.')]);

        return back();
    }

    public function reactivate(Request $request, Seller $seller): RedirectResponse
    {
        if ($seller->status !== SellerStatus::Suspended) {
            throw ValidationException::withMessages(['seller' => 'Toko ini tidak sedang dinonaktifkan.']);
        }

        $seller->approve($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Toko diaktifkan kembali.')]);

        return back();
    }
}
