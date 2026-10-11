<?php

namespace App\Http\Controllers\Account;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreReviewRequest;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class ReviewController extends Controller
{
    /**
     * Ulasan produk dari pesanan yang sudah selesai (satu ulasan per item pesanan).
     */
    public function store(StoreReviewRequest $request, Order $order, OrderItem $item): RedirectResponse
    {
        Gate::authorize('buyerAction', $order);

        abort_unless($item->order_id === $order->id && $item->product_id, 404);

        if ($order->status !== OrderStatus::Completed) {
            throw ValidationException::withMessages(['rating' => 'Ulasan hanya dapat diberikan setelah pesanan selesai.']);
        }

        if ($item->review()->exists()) {
            throw ValidationException::withMessages(['rating' => 'Produk ini sudah Anda ulas.']);
        }

        $images = collect($request->file('images', []))
            ->map(fn ($file) => $file->store('reviews', 'public'))
            ->all();

        $item->review()->create([
            'product_id' => $item->product_id,
            'user_id' => $request->user()->id,
            'rating' => $request->validated('rating'),
            'comment' => $request->validated('comment'),
            'images' => $images ?: null,
        ]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Ulasan terkirim.')]);

        return back();
    }
}
