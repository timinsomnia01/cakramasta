<?php

namespace App\Http\Controllers\Seller;

use App\Actions\Withdrawals\RequestWithdrawal;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\RequestWithdrawalRequest;
use App\Models\Withdrawal;
use App\Support\Presenters\WithdrawalPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WithdrawalController extends Controller
{
    /**
     * Saldo, formulir pencairan, dan riwayat pencairan hasil jual.
     */
    public function index(Request $request): Response
    {
        $seller = $request->user()->seller;

        return Inertia::render('seller/withdrawals/index', [
            'balance' => $seller->availableBalance(),
            'totalIncome' => $seller->totalIncome(),
            'minimum' => (int) config('marketplace.min_withdrawal'),
            'destinations' => [
                'qris' => (bool) $seller->qris_image,
                'bank' => $seller->bank_account_number ? [
                    'bank' => $seller->bank_name,
                    'number' => $seller->bank_account_number,
                    'holder' => $seller->bank_account_name,
                ] : null,
            ],
            'withdrawals' => $seller->withdrawals()
                ->latest()
                ->paginate(10)
                ->through(fn (Withdrawal $withdrawal) => WithdrawalPresenter::row($withdrawal)),
        ]);
    }

    public function store(RequestWithdrawalRequest $request, RequestWithdrawal $requestWithdrawal): RedirectResponse
    {
        $requestWithdrawal->handle(
            $request->user()->seller,
            (int) $request->validated('amount'),
            $request->validated('method'),
            $request->validated('notes'),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pengajuan pencairan terkirim.')]);

        return back();
    }
}
