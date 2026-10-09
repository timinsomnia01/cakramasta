<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CategoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('dashboard/categories/index', [
            'categories' => Category::latest()
                ->select(['id', 'slug', 'name', 'is_active'])
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('dashboard/categories/create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Category::create($this->validated($request));

        return $this->backToIndex($request, 'Kategori berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $current_team, Category $category)
    {
        return Inertia::render('dashboard/categories/edit', [
            'category' => $category->only(['id', 'slug', 'name', 'is_active']),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $current_team, Category $category)
    {
        $category->update($this->validated($request));

        return $this->backToIndex($request, 'Kategori berhasil diperbarui.');
    }

    public function destroy(Request $request, string $current_team, Category $category)
    {
        $category->delete();

        return $this->backToIndex($request, 'Kategori berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'slug'      => ['required', 'string', 'min:0'],
            'is_active' => ['required'],
        ]);
    }

    private function backToIndex(Request $request, string $message)
    {
        // Sesuaikan nama parameter team jika berbeda dari "current_team"
        return to_route('categories.index', ['current_team' => $request->route('current_team')])
            ->with('success', $message);
    }
}
