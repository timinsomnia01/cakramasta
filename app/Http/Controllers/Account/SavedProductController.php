<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Support\Presenters\ProductPresenter;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SavedProductController extends Controller
{
    /**
     * Daftar produk yang disimpan user.
     */
    public function index(Request $request): Response
    {
        $userId = $request->user()->id;

        return Inertia::render('account/saved-products', [
            'products' => ProductPresenter::cardQuery()
                ->whereHas('savers', fn ($query) => $query->where('users.id', $userId))
                ->latest('products.id')
                ->paginate(12)
                ->withQueryString()
                ->through(fn (Product $product) => ProductPresenter::card($product)),
        ]);
    }
}
