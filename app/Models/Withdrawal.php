<?php

namespace App\Models;

use App\Enums\WithdrawalStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    protected $fillable = [
        'code', 'seller_profile_id', 'amount', 'status', 'seller_note',
        'verifier_note', 'proof_path', 'processed_by', 'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'amount' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Withdrawal $w) {
            $w->code ??= 'WD-'.now()->format('Ymd').'-'.Str::upper(Str::random(5));
        });
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', WithdrawalStatus::Pending);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(SellerProfile::class, 'seller_profile_id');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
