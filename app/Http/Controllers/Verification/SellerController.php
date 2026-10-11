<?php

namespace App\Http\Controllers\Verification;

use App\Enums\SellerStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Verification\RejectionRequest;
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
        $status = SellerStatus::tryFrom((string) $request->query('status', 'pending'));
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('verification/sellers/index', [
            'sellers' => Seller::query()
                ->with('user:id,name,email')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('store_name', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))))
                ->oldest()
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

        return Inertia::render('verification/sellers/show', [
            'seller' => SellerPresenter::detail($seller),
            'canReview' => $seller->isPending(),
        ]);
    }

    public function approve(Request $request, Seller $seller): RedirectResponse
    {
        $this->ensurePending($seller);

        $seller->approve($request->user());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Penjual disetujui.')]);

        return to_route('verification.sellers.index');
    }

    public function reject(RejectionRequest $request, Seller $seller): RedirectResponse
    {
        $this->ensurePending($seller);

        $seller->reject($request->user(), $request->validated('reason'));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pendaftaran penjual ditolak.')]);

        return to_route('verification.sellers.index');
    }

    private function ensurePending(Seller $seller): void
    {
        if (! $seller->isPending()) {
            throw ValidationException::withMessages(['seller' => 'Pendaftaran ini sudah diproses.']);
        }
    }
}
