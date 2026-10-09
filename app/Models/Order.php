<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    protected $fillable = [
        'code', 'buyer_id', 'seller_profile_id', 'subtotal', 'admin_fee', 'total',
        'seller_earning', 'status', 'buyer_note', 'expired_at', 'paid_at',
        'completed_at', 'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'integer',
            'admin_fee' => 'integer',
            'total' => 'integer',
            'seller_earning' => 'integer',
            'expired_at' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Order $order) {
            $order->code ??= static::generateCode();
        });
    }

    public static function generateCode(): string
    {
        do {
            $code = 'UM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Ubah status sekaligus mencatat ke order_logs (dipakai untuk Log Pembelian).
     */
    public function changeStatus(OrderStatus $to, ?User $actor = null, ?string $note = null): void
    {
        $from = $this->status;

        $this->update(['status' => $to] + match ($to) {
            OrderStatus::Paid => ['paid_at' => now()],
            OrderStatus::Completed => ['completed_at' => now()],
            OrderStatus::Cancelled => ['cancelled_at' => now()],
            default => [],
        });

        $this->logs()->create([
            'actor_id' => $actor?->id,
            'from_status' => $from?->value,
            'to_status' => $to->value,
            'note' => $note,
        ]);
    }

    // ---- Scopes (Search & Filter di Log Pembelian) ----
    public function scopeStatus(Builder $q, ?string $status): Builder
    {
        return $status ? $q->where('status', $status) : $q;
    }

    public function scopeBetween(Builder $q, ?string $from, ?string $to): Builder
    {
        return $q->when($from, fn ($w) => $w->whereDate('created_at', '>=', $from))
            ->when($to, fn ($w) => $w->whereDate('created_at', '<=', $to));
    }

    public function scopeSearch(Builder $q, ?string $term): Builder
    {
        return $term
            ? $q->where(fn ($w) => $w->where('code', 'like', "%{$term}%")
                ->orWhereHas('buyer', fn ($b) => $b->where('name', 'like', "%{$term}%"))
                ->orWhereHas('seller', fn ($s) => $s->where('store_name', 'like', "%{$term}%")))
            : $q;
    }

    // ---- Relations ----
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class, 'seller_profile_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function logs(): HasMany
    {
        return $this->hasMany(OrderLog::class)->orderBy('created_at');
    }
}
