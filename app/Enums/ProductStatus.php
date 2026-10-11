<?php

namespace App\Enums;

/**
 * Status produk.
 */
enum ProductStatus: string
{
    case Draft = 'draft';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Inactive = 'inactive';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Pending => 'Menunggu Verifikasi',
            self::Approved => 'Aktif',
            self::Rejected => 'Ditolak',
            self::Inactive => 'Nonaktif',
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
