<?php

namespace App\Enums;

/**
 * Status pesanan / pembelian.
 */
enum OrderStatus: string
{
    case PendingPayment = 'pending_payment';
    case WaitingVerification = 'waiting_verification';
    case Paid = 'paid';
    case Processing = 'processing';
    case Shipped = 'shipped';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu Pembayaran',
            self::WaitingVerification => 'Menunggu Verifikasi Pembayaran',
            self::Paid => 'Dibayar',
            self::Processing => 'Diproses Penjual',
            self::Shipped => 'Dikirim',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
            self::Refunded => 'Dikembalikan',
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
