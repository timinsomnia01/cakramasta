<?php

namespace App\Enums;

/**
 * Status pembayaran.
 */
enum PaymentStatus: string
{
    case Pending = 'pending';
    case WaitingVerification = 'waiting_verification';
    case Verified = 'verified';
    case Rejected = 'rejected';
    case Expired = 'expired';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Belum Dibayar',
            self::WaitingVerification => 'Menunggu Verifikasi',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
            self::Expired => 'Kedaluwarsa',
        };
    }

    /**
     * Get all enum values.
     *
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
