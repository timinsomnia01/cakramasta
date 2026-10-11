<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $code
 * @property int $seller_id
 * @property int $amount
 * @property string $method
 * @property string|null $qris_image
 * @property string|null $bank_name
 * @property string|null $bank_account_number
 * @property string|null $bank_account_name
 * @property WithdrawalStatus $status
 * @property string|null $notes
 * @property string|null $rejection_reason
 * @property string|null $transfer_proof
 * @property int|null $processed_by
 * @property Carbon|null $processed_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Seller $seller
 * @property-read User|null $processor
 */
#[Fillable([
    'seller_id', 'amount', 'method', 'qris_image',
    'bank_name', 'bank_account_number', 'bank_account_name', 'notes',
])]
class Withdrawal extends Model
{
    use HasFactory;

    /**
     * Bootstrap the model and its traits.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Withdrawal $withdrawal) {
            if (empty($withdrawal->code)) {
                do {
                    $code = 'WD-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
                } while (static::where('code', $code)->exists());

                $withdrawal->code = $code;
            }
        });
    }

    /**
     * @return BelongsTo<Seller, $this>
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    /**
     * The verifikator / admin who processed the withdrawal.
     *
     * @return BelongsTo<User, $this>
     */
    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    /**
     * Determine if the withdrawal is pending.
     */
    public function isPending(): bool
    {
        return $this->status === WithdrawalStatus::Pending;
    }

    /**
     * Mark the withdrawal as paid (sudah ditransfer).
     */
    public function markAsPaid(User $processor, ?string $transferProof = null): bool
    {
        return $this->forceFill([
            'status' => WithdrawalStatus::Paid,
            'transfer_proof' => $transferProof,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ])->save();
    }

    /**
     * Mark the withdrawal as rejected.
     */
    public function reject(User $processor, string $reason): bool
    {
        return $this->forceFill([
            'status' => WithdrawalStatus::Rejected,
            'rejection_reason' => $reason,
            'processed_by' => $processor->id,
            'processed_at' => now(),
        ])->save();
    }

    /**
     * @param  Builder<Withdrawal>  $query
     */
    public function scopePending(Builder $query): void
    {
        $query->where('status', WithdrawalStatus::Pending);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'status' => WithdrawalStatus::class,
            'processed_at' => 'datetime',
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
