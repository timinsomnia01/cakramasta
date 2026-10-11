<?php

namespace Database\Seeders;

use App\Models\Category;
use Database\Seeders\Support\CatalogData;
use Database\Seeders\Support\PlaceholderImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Kategori utama beserta sub kategorinya.
 */
class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (CatalogData::categories() as $index => $data) {
            $root = Category::factory()->create([
                'parent_id' => null,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'image' => PlaceholderImage::public("categories/{$data['slug']}.svg", $data['name'], $index),
                'description' => $data['description'],
                'sort_order' => $index + 1,
                'is_active' => true,
            ]);

            foreach ($data['subs'] as $subIndex => $sub) {
                $slug = Str::slug($sub['name']);

                Category::factory()->create([
                    'parent_id' => $root->id,
                    'name' => $sub['name'],
                    'slug' => $slug,
                    'image' => PlaceholderImage::public("categories/{$slug}.svg", $sub['name'], $index + $subIndex + 1),
                    'description' => $sub['description'],
                    'sort_order' => $subIndex + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
