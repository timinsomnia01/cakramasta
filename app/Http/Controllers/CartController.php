<?php

namespace App\Http\Controllers;

use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Models\CartItem;
use App\Support\Presenters\CartPresenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CartController extends Controller
{
    /**
     * Isi keranjang, dikelompokkan per penjual.
     */
    public function index(Request $request): Response
    {
        $items = $request->user()->cartItems()
            ->with(CartPresenter::relations())
            ->latest()
            ->get();

        return Inertia::render('cart/index', [
            'groups' => CartPresenter::groups($items),
            'count' => $items->count(),
        ]);
    }

    /**
     * Tambah produk ke keranjang. Jika produk (dan varian) yang sama sudah ada, jumlahnya ditambah.
     */
    public function store(AddToCartRequest $request): RedirectResponse
    {
        $user = $request->user();
        $variantId = $request->integer('product_variant_id') ?: null;

        $item = $user->cartItems()->firstOrNew([
            'product_id' => $request->integer('product_id'),
            'product_variant_id' => $variantId,
        ]);

        $item->quantity = ($item->exists ? $item->quantity : 0) + $request->integer('quantity');
        $item->save();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk ditambahkan ke keranjang.')]);

        return back();
    }

    /**
     * Ubah jumlah item.
     */
    public function update(UpdateCartItemRequest $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);

        $cartItem->update(['quantity' => $request->integer('quantity')]);

        return back();
    }

    /**
     * Hapus item dari keranjang.
     */
    public function destroy(Request $request, CartItem $cartItem): RedirectResponse
    {
        abort_unless($cartItem->user_id === $request->user()->id, 404);

        $cartItem->delete();

        return back();
    }
}
