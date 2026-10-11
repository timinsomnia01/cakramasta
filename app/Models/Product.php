<?php

namespace App\Models;

use App\Concerns\GeneratesUniqueSlugs;
use App\Enums\ProductStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $seller_id
 * @property int $category_id
 * @property string $name
 * @property string $slug
 * @property string|null $sku
 * @property string $description
 * @property int $price
 * @property int $stock
 * @property int $weight
 * @property string $condition
 * @property ProductStatus $status
 * @property string|null $rejection_reason
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property int $view_count
 * @property int $sold_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Seller $seller
 * @property-read Category $category
 * @property-read User|null $verifier
 * @property-read Collection<int, ProductImage> $images
 * @property-read ProductImage|null $primaryImage
 * @property-read Collection<int, ProductVariant> $variants
 * @property-read Collection<int, Review> $reviews
 * @property-read Collection<int, User> $likers
 * @property-read Collection<int, User> $savers
 */
#[Fillable([
    'seller_id', 'category_id', 'name', 'slug', 'sku', 'description',
    'price', 'stock', 'weight', 'condition', 'status',
])]
class Product extends Model
{
    use GeneratesUniqueSlugs, HasFactory, SoftDeletes;

    /**
     * @return BelongsTo<Seller, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the verifikator / admin who verified the product.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Gambar produk (gambar utama + gambar pendukung untuk carousel).
     *
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->ofMany([
            'is_primary' => 'max',
            'id' => 'min',
        ]);
    }

    /**
     * @return HasMany<ProductVariant, $this>
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Users who liked this product.
     *
     * @return BelongsToMany<User, $this>
     */
    public function likers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_likes')->withTimestamps();
    }

    /**
     * Users who saved this product.
     *
     * @return BelongsToMany<User, $this>
     */
    public function savers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'product_saves')->withTimestamps();
    }

    /**
     * Determine if the product has variants.
     */
    public function hasVariants(): bool
    {
        return $this->variants()->exists();
    }

    /**
     * Determine if the product is approved (active & visible).
     */
    public function isApproved(): bool
    {
        return $this->status === ProductStatus::Approved;
    }

    /**
     * Mark the product as approved.
     */
    public function approve(User $verifier): bool
    {
        return $this->forceFill([
            'status' => ProductStatus::Approved,
            'rejection_reason' => null,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ])->save();
    }

    /**
     * Mark the product as rejected.
     */
    public function reject(User $verifier, string $reason): bool
    {
        return $this->forceFill([
            'status' => ProductStatus::Rejected,
            'rejection_reason' => $reason,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ])->save();
    }

    /**
     * Increment the view counter without touching updated_at.
     */
    public function recordView(): void
    {
        $this->timestamps = false;
        $this->increment('view_count');
        $this->timestamps = true;
    }

    /**
     * Statistik untuk halaman detail produk (like, view, ulasan, disimpan).
     *
     * @param  Builder<Product>  $query
     */
    public function scopeWithStats(Builder $query): void
    {
        $query->withCount(['likers as likes_count', 'savers as saves_count', 'reviews'])
            ->withAvg('reviews', 'rating');
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', ProductStatus::Approved);
    }

    /**
     * @param  Builder<Product>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', ProductStatus::Pending);
    }

    /**
     * Pencarian nama produk.
     *
     * @param  Builder<Product>  $query
     */
    public function scopeSearch(Builder $query, ?string $keyword): void
    {
        $query->when($keyword, fn (Builder $q) => $q->where('name', 'like', "%{$keyword}%"));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'stock' => 'integer',
            'weight' => 'integer',
            'status' => ProductStatus::class,
            'verified_at' => 'datetime',
            'view_count' => 'integer',
            'sold_count' => 'integer',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
