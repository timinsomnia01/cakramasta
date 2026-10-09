<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductReview extends Model
{
    protected $fillable = ['product_id', 'user_id', 'order_item_id', 'rating', 'comment', 'is_visible'];

    protected function casts(): array
    {
        return ['rating' => 'integer', 'is_visible' => 'boolean'];
    }

    protected static function booted(): void
    {
        $sync = fn (ProductReview $r) => $r->product?->syncReviewStats();

        static::saved($sync);
        static::deleted($sync);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }
}
