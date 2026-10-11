<?php

namespace Database\Seeders\Support;

use Carbon\CarbonInterface;

/**
 * Helper pembuat data berformat Indonesia (alamat Kota Malang, NIK, nomor HP, rekening, kode invoice).
 * Semua acak memakai Faker, jadi hasilnya ikut deterministik saat fake()->seed() dipanggil.
 */
class IndonesianData
{
    /**
     * Kecamatan di Kota Malang beserta kode pos dan kode kecamatan NIK.
     *
     * @var array<string, array{0: string, 1: string}>
     */
    private const DISTRICTS = [
        'Lowokwaru' => ['65141', '01'],
        'Klojen' => ['65111', '02'],
        'Blimbing' => ['65126', '03'],
        'Sukun' => ['65147', '04'],
        'Kedungkandang' => ['65138', '05'],
    ];

    /**
     * @var list<string>
     */
    private const STREETS = [
        'Veteran', 'Soekarno Hatta', 'Gajayana', 'Semarang', 'Bandung', 'Kawi', 'Ijen',
        'Dieng', 'Sigura-gura', 'Bunga Merak', 'Candi Panggung', 'Tlogomas', 'Joyo Agung',
        'Mayjen Panjaitan', 'Jombang', 'Simpang Borobudur', 'Raya Langsep', 'Sumbersari',
        'Bendungan Sutami', 'Kerto Raharjo', 'Galunggung', 'Danau Toba', 'Borobudur',
    ];

    /**
     * @var list<string>
     */
    private const PHONE_PREFIXES = [
        '0811', '0812', '0813', '0821', '0822', '0852', '0857', '0878', '0895', '0896',
    ];

    /**
     * @var array<string, string>
     */
    private const BANKS = [
        'BCA' => '##########',
        'BRI' => '###############',
        'BNI' => '##########',
        'Mandiri' => '#############',
    ];

    /**
     * Nomor HP seluler, contoh: 081234567890.
     */
    public static function phone(): string
    {
        return fake()->randomElement(self::PHONE_PREFIXES).fake()->numerify('#######');
    }

    /**
     * Alamat di Kota Malang, contoh: Jl. Gajayana No. 14, Kec. Lowokwaru, Kota Malang 65141.
     * Kecamatan bisa ditentukan agar alamat penjual konsisten.
     */
    public static function address(?string $district = null): string
    {
        $district ??= fake()->randomElement(array_keys(self::DISTRICTS));
        $postal = self::DISTRICTS[$district][0] ?? '65145';

        return sprintf(
            'Jl. %s No. %d, Kec. %s, Kota Malang, Jawa Timur %s',
            fake()->randomElement(self::STREETS),
            fake()->numberBetween(1, 120),
            $district,
            $postal,
        );
    }

    /**
     * NIK 16 digit berformat valid (provinsi 35 Jawa Timur, kabupaten/kota 73 Malang).
     * Ini data karangan, bukan NIK milik orang sungguhan.
     */
    public static function nik(bool $female = false): string
    {
        $district = fake()->randomElement(array_values(self::DISTRICTS))[1];
        $day = fake()->numberBetween(1, 28) + ($female ? 40 : 0);
        $month = fake()->numberBetween(1, 12);
        $year = fake()->numberBetween(70, 104) % 100; // tahun lahir 1970-2004

        return sprintf(
            '3573%s%02d%02d%02d%s',
            $district,
            $day,
            $month,
            $year,
            fake()->numerify('####'),
        );
    }

    /**
     * Bank dan nomor rekening karangan.
     *
     * @return array{bank: string, number: string}
     */
    public static function bankAccount(): array
    {
        $bank = fake()->randomElement(array_keys(self::BANKS));

        return [
            'bank' => $bank,
            'number' => fake()->numerify(self::BANKS[$bank]),
        ];
    }

    /**
     * Kode unik berformat PREFIX-YYYYMMDD-XXXXXX.
     */
    public static function code(string $prefix, CarbonInterface $date): string
    {
        return sprintf(
            '%s-%s-%s',
            $prefix,
            $date->format('Ymd'),
            fake()->unique()->regexify('[A-Z0-9]{6}'),
        );
    }
}
