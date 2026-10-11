<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductSaveController extends Controller
{
    /**
     * Simpan produk ke daftar tersimpan (wishlist).
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isApproved(), 404);

        $request->user()->savedProducts()->syncWithoutDetaching([$product->id]);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Produk disimpan.')]);

        return back();
    }

    /**
     * Hapus produk dari daftar tersimpan.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->savedProducts()->detach($product->id);

        return back();
    }
}
