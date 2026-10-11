<?php

namespace App\Models;

use App\Concerns\GeneratesUniqueSlugs;
use App\Enums\OrderStatus;
use App\Enums\SellerStatus;
use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property string $store_name
 * @property string $slug
 * @property string|null $description
 * @property string $phone
 * @property string $address
 * @property string|null $logo
 * @property string $ktp_number
 * @property string $ktp_image
 * @property string $selfie_image
 * @property string $qris_image
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property SellerStatus $status
 * @property string|null $rejection_reason
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read User|null $verifier
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, Order> $orders
 * @property-read Collection<int, Withdrawal> $withdrawals
 */
#[Fillable([
    'user_id', 'store_name', 'slug', 'description', 'phone', 'address', 'logo',
    'ktp_number', 'ktp_image', 'selfie_image', 'qris_image',
    'bank_name', 'bank_account_number', 'bank_account_name',
])]
#[Hidden(['ktp_number', 'ktp_image', 'selfie_image'])]
class Seller extends Model
{
    use GeneratesUniqueSlugs, HasFactory, SoftDeletes;

    /**
     * Get the column used as the slug source.
     */
    public function slugSourceColumn(): string
    {
        return 'store_name';
    }

    /**
     * Get the user who owns the store.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the verifikator / admin who verified the seller.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * @return HasMany<Withdrawal, $this>
     */
    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    /**
     * Total pendapatan dari pesanan yang sudah selesai.
     */
    public function totalIncome(): int
    {
        return (int) $this->orders()
            ->where('status', OrderStatus::Completed)
            ->sum('seller_income');
    }

    /**
     * Saldo yang bisa dicairkan (pendapatan - pencairan yang tidak ditolak).
     */
    public function availableBalance(): int
    {
        $withdrawn = (int) $this->withdrawals()
            ->where('status', '!=', WithdrawalStatus::Rejected)
            ->sum('amount');

        return max(0, $this->totalIncome() - $withdrawn);
    }

    /**
     * Determine if the seller has been approved.
     */
    public function isApproved(): bool
    {
        return $this->status === SellerStatus::Approved;
    }

    /**
     * Determine if the seller is waiting for verification.
     */
    public function isPending(): bool
    {
        return $this->status === SellerStatus::Pending;
    }

    /**
     * Mark the seller as approved.
     */
    public function approve(User $verifier): bool
    {
        return $this->forceFill([
            'status' => SellerStatus::Approved,
            'rejection_reason' => null,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ])->save();
    }

    /**
     * Mark the seller as rejected.
     */
    public function reject(User $verifier, string $reason): bool
    {
        return $this->forceFill([
            'status' => SellerStatus::Rejected,
            'rejection_reason' => $reason,
            'verified_by' => $verifier->id,
            'verified_at' => now(),
        ])->save();
    }

    /**
     * @param  Builder<Seller>  $query
     */
    public function scopeApproved(Builder $query): void
    {
        $query->where('status', SellerStatus::Approved);
    }

    /**
     * @param  Builder<Seller>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', SellerStatus::Pending);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => SellerStatus::class,
            'verified_at' => 'datetime',
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
