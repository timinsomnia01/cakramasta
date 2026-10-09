<?php

namespace App\Models;

use App\Enums\VerificationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class SellerProfile extends Model
{
    protected $fillable = [
        'user_id', 'store_name', 'slug', 'store_description', 'nik', 'ktp_photo',
        'selfie_photo', 'qris_image', 'status', 'rejection_note',
        'verified_by', 'verified_at', 'balance',
    ];

    protected $hidden = ['nik'];

    protected function casts(): array
    {
        return [
            'status' => VerificationStatus::class,
            'verified_at' => 'datetime',
            'balance' => 'integer',
            // 'nik' => 'encrypted', // aktifkan jika ingin NIK dienkripsi (ubah kolom jadi text)
        ];
    }

    public function isApproved(): bool
    {
        return $this->status === VerificationStatus::Approved;
    }

    public function scopeApproved(Builder $q): Builder
    {
        return $q->where('status', VerificationStatus::Approved);
    }

    public function scopePending(Builder $q): Builder
    {
        return $q->where('status', VerificationStatus::Pending);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }
}
