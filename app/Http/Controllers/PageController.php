<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Halaman statis footer (Guide, Cara Jual Produk, Cara Beli Produk, Peraturan, Kesepakatan Pajak).
     * Konten berupa HTML, tampilkan dengan dangerouslySetInnerHTML / v-html.
     */
    public function show(Page $page): Response
    {
        abort_unless($page->is_published, 404);

        return Inertia::render('pages/show', [
            'page' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'content' => $page->content,
                'updatedAt' => $page->updated_at->toISOString(),
            ],
        ]);
    }
}
