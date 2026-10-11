<?php

namespace App\Http\Controllers\Verification;

use App\Enums\WithdrawalStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Verification\MarkWithdrawalPaidRequest;
use App\Http\Requests\Verification\RejectionRequest;
use App\Models\Withdrawal;
use App\Support\Presenters\WithdrawalPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class WithdrawalController extends Controller
{
    /**
     * Alur: pending → (disetujui) approved → (bukti transfer diunggah) paid, atau rejected.
     * Saldo penjual hanya berkurang oleh pencairan yang tidak ditolak, jadi penolakan mengembalikan saldo.
     */
    public function index(Request $request): Response
    {
        $status = WithdrawalStatus::tryFrom((string) $request->query('status', 'pending'));
        $search = trim((string) $request->query('q', ''));

        return Inertia::render('verification/withdrawals/index', [
            'withdrawals' => Withdrawal::query()
                ->with('seller:id,store_name')
                ->when($status, fn ($query) => $query->where('status', $status))
                ->when($search !== '', fn ($query) => $query->where(fn ($query) => $query
                    ->where('code', 'like', "%{$search}%")
                    ->orWhereHas('seller', fn ($query) => $query->where('store_name', 'like', "%{$search}%"))))
                ->oldest()
                ->paginate(15)
                ->withQueryString()
                ->through(fn (Withdrawal $withdrawal) => WithdrawalPresenter::row($withdrawal)),
            'filters' => ['status' => $status?->value, 'q' => $search],
            'statuses' => collect(WithdrawalStatus::cases())->map(fn (WithdrawalStatus $s) => ['value' => $s->value, 'label' => $s->label()]),
        ]);
    }

    public function show(Withdrawal $withdrawal): Response
    {
        $withdrawal->load(['seller', 'processor:id,name']);

        return Inertia::render('verification/withdrawals/show', [
            'withdrawal' => WithdrawalPresenter::row($withdrawal),
            'sellerBalance' => $withdrawal->seller->availableBalance(),
            'can' => [
                'approve' => $withdrawal->status === WithdrawalStatus::Pending,
                'reject' => in_array($withdrawal->status, [WithdrawalStatus::Pending, WithdrawalStatus::Approved], true),
                'markPaid' => $withdrawal->status === WithdrawalStatus::Approved,
            ],
        ]);
    }

    public function approve(Request $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->transition($withdrawal, [WithdrawalStatus::Pending], function (Withdrawal $withdrawal) use ($request) {
            $withdrawal->forceFill([
                'status' => WithdrawalStatus::Approved,
                'processed_by' => $request->user()->id,
                'processed_at' => now(),
            ])->save();
        });

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pencairan disetujui. Lakukan transfer lalu unggah bukti.')]);

        return back();
    }

    public function reject(RejectionRequest $request, Withdrawal $withdrawal): RedirectResponse
    {
        $this->transition(
            $withdrawal,
            [WithdrawalStatus::Pending, WithdrawalStatus::Approved],
            fn (Withdrawal $withdrawal) => $withdrawal->reject($request->user(), $request->validated('reason')),
        );

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pencairan ditolak. Saldo dikembalikan ke penjual.')]);

        return to_route('verification.withdrawals.index');
    }

    public function markPaid(MarkWithdrawalPaidRequest $request, Withdrawal $withdrawal): RedirectResponse
    {
        $path = $request->file('transfer_proof')->store('transfer-proofs', 'local');

        try {
            $this->transition(
                $withdrawal,
                [WithdrawalStatus::Approved],
                fn (Withdrawal $withdrawal) => $withdrawal->markAsPaid($request->user(), $path),
            );
        } catch (ValidationException $exception) {
            Storage::disk('local')->delete($path);

            throw $exception;
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Pencairan ditandai sudah dibayar.')]);

        return to_route('verification.withdrawals.index');
    }

    /**
     * Jalankan perubahan status dengan baris terkunci agar dua verifikator tidak memproses bersamaan.
     *
     * @param  list<WithdrawalStatus>  $from
     * @param  callable(Withdrawal): mixed  $change
     */
    private function transition(Withdrawal $withdrawal, array $from, callable $change): void
    {
        DB::transaction(function () use ($withdrawal, $from, $change) {
            $locked = Withdrawal::query()->lockForUpdate()->findOrFail($withdrawal->id);

            if (! in_array($locked->status, $from, true)) {
                throw ValidationException::withMessages(['withdrawal' => 'Status pencairan sudah berubah. Muat ulang halaman.']);
            }

            $change($locked);
        });
    }
}
