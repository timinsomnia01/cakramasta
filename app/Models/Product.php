<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_profile_id', 'category_id', 'name', 'slug', 'description', 'price',
        'stock', 'weight_gram', 'status', 'rejection_note', 'verified_by', 'verified_at',
        'view_count', 'like_count', 'save_count', 'review_count', 'sold_count', 'rating_avg',
    ];

    protected function casts(): array
    {
        return [
            'status' => ProductStatus::class,
            'price' => 'integer',
            'rating_avg' => 'float',
            'verified_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::uniqueSlug($product->name);
            }
        });
    }

    public static function uniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 1;

        while (static::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $original.'-'.$i++;
        }

        return $slug;
    }

    /** Route: /kategori/{category:slug}/produk/{product:slug} */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** Hitung ulang jumlah & rata-rata rating dari ulasan yang tampil. */
    public function syncReviewStats(): void
    {
        $stats = $this->reviews()->where('is_visible', true)
            ->selectRaw('COUNT(*) as total, COALESCE(AVG(rating), 0) as avg_rating')
            ->first();

        $this->forceFill([
            'review_count' => (int) $stats->total,
            'rating_avg' => round((float) $stats->avg_rating, 2),
        ])->saveQuietly();
    }

    /** Hitung ulang like & save (panggil setelah toggle like/save). */
    public function syncInteractionCounts(): void
    {
        $this->forceFill([
            'like_count' => $this->likedBy()->count(),
            'save_count' => $this->savedBy()->count(),
        ])->saveQuietly();
    }

    // ---- Scopes ----
    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', ProductStatus::Approved);
    }

    public function scopePendingVerification(Builder $q): Builder
    {
        return $q->where('status', ProductStatus::Pending);
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term ? $q->where('name', 'like', "%{$term}%") : $q;
    }

    // ---- Accessors ----
    public function getPrimaryImageAttribute(): ?string
    {
        $img = $this->relationLoaded('images')
            ? ($this->images->firstWhere('is_primary', true) ?? $this->images->first())
            : ($this->images()->where('is_primary', true)->first() ?? $this->images()->first());

        return $img?->path;
    }

    // ---- Relations ----
    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class, 'seller_profile_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function likedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_likes')->withTimestamps();
    }

    public function savedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_saves')->withTimestamps();
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
