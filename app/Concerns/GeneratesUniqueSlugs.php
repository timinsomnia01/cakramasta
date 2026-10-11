<?php

namespace App\Concerns;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Generate slug unik untuk model (kategori, produk, toko, halaman).
 *
 * Model bisa override:
 *  - slugSourceColumn(): kolom sumber slug (default 'name')
 */
trait GeneratesUniqueSlugs
{
    /**
     * Boot the trait.
     */
    protected static function bootGeneratesUniqueSlugs(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model->{$model->slugSourceColumn()});
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->slugSourceColumn()) && ! $model->isDirty('slug')) {
                $model->slug = static::generateUniqueSlug($model->{$model->slugSourceColumn()}, $model->getKey());
            }
        });
    }

    /**
     * Get the column used as the slug source.
     */
    public function slugSourceColumn(): string
    {
        return 'name';
    }

    /**
     * Generate a unique slug for the given value.
     */
    public static function generateUniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::lower(Str::random(8));
        $slug = $base;
        $suffix = 2;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = "{$base}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }

    /**
     * Determine if the slug is already taken (including soft deleted rows).
     */
    protected static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = in_array(SoftDeletes::class, class_uses_recursive(static::class))
            ? static::withTrashed()
            : static::query();

        return $query
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
            ->exists();
    }
}
