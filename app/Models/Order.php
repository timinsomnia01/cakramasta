<?php

namespace App\Models;

use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $code
 * @property int $user_id
 * @property int $seller_id
 * @property OrderStatus $status
 * @property int $subtotal
 * @property int $shipping_cost
 * @property int $platform_fee
 * @property int $tax_amount
 * @property int $total
 * @property int $seller_income
 * @property string $recipient_name
 * @property string $recipient_phone
 * @property string $shipping_address
 * @property string|null $courier
 * @property string|null $tracking_number
 * @property string|null $notes
 * @property Carbon|null $paid_at
 * @property Carbon|null $shipped_at
 * @property Carbon|null $completed_at
 * @property Carbon|null $cancelled_at
 * @property string|null $cancel_reason
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $buyer
 * @property-read Seller $seller
 * @property-read Collection<int, OrderItem> $items
 * @property-read Collection<int, Payment> $payments
 * @property-read Payment|null $latestPayment
 * @property-read Collection<int, OrderStatusLog> $statusLogs
 */
#[Fillable([
    'user_id', 'seller_id', 'status',
    'subtotal', 'shipping_cost', 'platform_fee', 'tax_amount', 'total', 'seller_income',
    'recipient_name', 'recipient_phone', 'shipping_address',
    'courier', 'tracking_number', 'notes',
    'paid_at', 'shipped_at', 'completed_at', 'cancelled_at', 'cancel_reason',
])]
class Order extends Model
{
    use HasFactory;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Order $order) {
            if (empty($order->code)) {
                $order->code = static::generateCode();
            }
        });

        static::created(function (Order $order) {
            $order->statusLogs()->create([
                'status' => $order->status,
                'changed_by' => auth()->id(),
            ]);
        });
    }

    /**
     * Generate a unique invoice code, e.g. UM-20261010-AB12CD.
     */
    public static function generateCode(): string
    {
        do {
            $code = 'UM-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (static::where('code', $code)->exists());

        return $code;
    }

    /**
     * Get the buyer.
     *
     * @return BelongsTo<User, $this>
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return BelongsTo<Seller, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Payment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * @return HasOne<Payment, $this>
     */
    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    /**
     * Histori / log perubahan status pesanan.
     *
     * @return HasMany<OrderStatusLog, $this>
     */
    public function statusLogs(): HasMany
    {
        return $this->hasMany(OrderStatusLog::class)->latest();
    }

    /**
     * Ubah status pesanan sekaligus mencatat log-nya.
     */
    public function transitionTo(OrderStatus $status, ?User $by = null, ?string $note = null): bool
    {
        $timestamps = [
            OrderStatus::Paid->value => 'paid_at',
            OrderStatus::Shipped->value => 'shipped_at',
            OrderStatus::Completed->value => 'completed_at',
            OrderStatus::Cancelled->value => 'cancelled_at',
        ];

        $this->status = $status;

        if (isset($timestamps[$status->value])) {
            $this->{$timestamps[$status->value]} = now();
        }

        if (! $this->save()) {
            return false;
        }

        $this->statusLogs()->create([
            'status' => $status,
            'note' => $note,
            'changed_by' => $by?->id ?? auth()->id(),
        ]);

        return true;
    }

    /**
     * Filter untuk log pembelian (search & filter admin / export excel).
     *
     * @param  Builder<Order>  $query
     * @param  array{search?: string|null, status?: string|null, seller_id?: int|null, from?: string|null, to?: string|null}  $filters
     */
    public function scopeFilter(Builder $query, array $filters): void
    {
        $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('recipient_name', 'like', "%{$search}%")
                        ->orWhereHas('buyer', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $q->where('status', $status))
            ->when($filters['seller_id'] ?? null, fn (Builder $q, int $id) => $q->where('seller_id', $id))
            ->when($filters['from'] ?? null, fn (Builder $q, string $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn (Builder $q, string $to) => $q->whereDate('created_at', '<=', $to));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'integer',
            'shipping_cost' => 'integer',
            'platform_fee' => 'integer',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'seller_income' => 'integer',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'code';
    }
}
