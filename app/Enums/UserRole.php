<?php

namespace App\Enums;

/**
 * Role pengguna aplikasi.
 */
enum UserRole: string
{
    case User = 'user';
    case Verifikator = 'verifikator';
    case Admin = 'admin';

    /**
     * Get the human readable label.
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'Pengguna',
            self::Verifikator => 'Verifikator',
            self::Admin => 'Admin',
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
