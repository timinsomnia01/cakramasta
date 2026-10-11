<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProductLikeController extends Controller
{
    /**
     * Sukai produk. Aman dipanggil berulang (tidak membuat data ganda).
     */
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->isApproved(), 404);

        $request->user()->likedProducts()->syncWithoutDetaching([$product->id]);

        return back();
    }

    /**
     * Batalkan suka.
     */
    public function destroy(Request $request, Product $product): RedirectResponse
    {
        $request->user()->likedProducts()->detach($product->id);

        return back();
    }
}
