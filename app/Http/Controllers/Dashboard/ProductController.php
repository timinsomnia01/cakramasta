<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index()
    {
        return Inertia::render('dashboard/products/index', [
            'products' => Product::latest()
                ->select(['id', 'slug', 'name', 'status', 'price', 'stock'])
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return Inertia::render('dashboard/products/create');
    }

    public function store(Request $request)
    {
        Product::create($this->validated($request));

        return $this->backToIndex($request, 'Produk berhasil ditambahkan.');
    }

    public function edit(string $current_team, Product $product)
    {
        return Inertia::render('dashboard/products/edit', [
            'product' => $product->only(['id', 'slug', 'name', 'description', 'price', 'stock']),
        ]);
    }

    public function update(Request $request, string $current_team, Product $product)
    {
        $product->update($this->validated($request));

        return $this->backToIndex($request, 'Produk berhasil diperbarui.');
    }

    public function destroy(Request $request, string $current_team, Product $product)
    {
        $product->delete();

        return $this->backToIndex($request, 'Produk berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'integer', 'min:0'],
            'stock'       => ['required', 'integer', 'min:0'],
        ]);
    }

    private function backToIndex(Request $request, string $message)
    {
        // Sesuaikan nama parameter team jika berbeda dari "current_team"
        return to_route('products.index', ['current_team' => $request->route('current_team')])
            ->with('success', $message);
    }
}
