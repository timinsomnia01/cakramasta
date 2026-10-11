<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use Database\Seeders\Support\CatalogData;
use Illuminate\Database\Seeder;

/**
 * Halaman statis footer: Guide, Cara Jual Produk, Cara Beli Produk, Peraturan, Kesepakatan Pajak.
 */
class PageSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::role(UserRole::Admin)->firstOrFail();

        foreach (CatalogData::pages() as $index => $page) {
            Page::factory()->create([
                'title' => $page['title'],
                'slug' => $page['slug'],
                'content' => $page['content'],
                'is_published' => true,
                'sort_order' => $index + 1,
                'updated_by' => $admin->id,
                'created_at' => now()->subDays(100),
                'updated_at' => now()->subDays(fake()->numberBetween(1, 30)),
            ]);
        }
    }
}
