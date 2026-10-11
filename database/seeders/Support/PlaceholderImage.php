<?php

namespace Database\Seeders\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Membuat gambar SVG placeholder agar halaman produk, kategori, dan dokumen tidak kosong
 * saat seeding. Ganti dengan foto asli lewat fitur upload aplikasi.
 *
 * Gambar publik (produk, kategori, logo) ditulis ke disk "public".
 * Dokumen sensitif (KTP, selfie, QRIS, bukti bayar) ditulis ke disk "local" (privat).
 */
class PlaceholderImage
{
    /**
     * Gambar produk / kategori / logo, disimpan di disk public.
     */
    public static function public(string $path, string $title, int $variant = 0, string $subtitle = ''): string
    {
        return self::write('public', $path, self::card($title, $subtitle, $variant, 800, 800));
    }

    /**
     * Foto KTP karangan, disimpan di disk privat.
     */
    public static function ktp(string $path, string $name, string $nik): string
    {
        $svg = self::open(640, 400, '#dbeafe')
            .'<rect x="16" y="16" width="608" height="368" rx="18" fill="#eff6ff" stroke="#2563eb" stroke-width="3"/>'
            .'<text x="320" y="62" text-anchor="middle" font-family="sans-serif" font-size="22" font-weight="700" fill="#1e3a8a">KARTU TANDA PENDUDUK (CONTOH)</text>'
            .'<text x="48" y="130" font-family="monospace" font-size="24" fill="#111827">NIK  : '.self::e($nik).'</text>'
            .'<text x="48" y="176" font-family="sans-serif" font-size="22" fill="#111827">Nama : '.self::e($name).'</text>'
            .'<rect x="456" y="110" width="140" height="180" rx="8" fill="#bfdbfe"/>'
            .'<circle cx="526" cy="170" r="32" fill="#60a5fa"/><rect x="486" y="212" width="80" height="60" rx="30" fill="#60a5fa"/>'
            .'</svg>';

        return self::write('local', $path, $svg);
    }

    /**
     * Foto selfie memegang KTP (ilustrasi), disimpan di disk privat.
     */
    public static function selfie(string $path, string $name): string
    {
        $svg = self::open(600, 800, '#fef3c7')
            .'<circle cx="300" cy="300" r="110" fill="#fcd34d"/>'
            .'<rect x="170" y="430" width="260" height="260" rx="120" fill="#f59e0b"/>'
            .'<rect x="380" y="560" width="150" height="96" rx="8" fill="#eff6ff" stroke="#2563eb" stroke-width="3"/>'
            .'<text x="300" y="760" text-anchor="middle" font-family="sans-serif" font-size="26" fill="#78350f">'.self::e($name).'</text>'
            .'</svg>';

        return self::write('local', $path, $svg);
    }

    /**
     * Gambar mirip QRIS (pola kotak acak, tidak bisa discan), disimpan di disk privat.
     */
    public static function qris(string $path, string $storeName): string
    {
        $cells = 25;
        $size = 16;
        $svg = self::open($cells * $size + 80, $cells * $size + 150, '#ffffff')
            .'<text x="50%" y="42" text-anchor="middle" font-family="sans-serif" font-size="26" font-weight="700" fill="#b91c1c">QRIS</text>'
            .'<g transform="translate(40,64)">';

        for ($y = 0; $y < $cells; $y++) {
            for ($x = 0; $x < $cells; $x++) {
                $finder = ($x < 7 && $y < 7) || ($x > 17 && $y < 7) || ($x < 7 && $y > 17);

                if ($finder ? self::finderCell($x, $y, $cells) : fake()->boolean(48)) {
                    $svg .= '<rect x="'.($x * $size).'" y="'.($y * $size).'" width="'.$size.'" height="'.$size.'" fill="#111827"/>';
                }
            }
        }

        $svg .= '</g><text x="50%" y="'.($cells * $size + 110).'" text-anchor="middle" font-family="sans-serif" font-size="20" fill="#111827">'
            .self::e($storeName).'</text></svg>';

        return self::write('local', $path, $svg);
    }

    /**
     * Bukti transfer / pembayaran (ilustrasi), disimpan di disk privat.
     */
    public static function proof(string $path, string $code, int $amount, string $method): string
    {
        $svg = self::open(480, 640, '#f0fdf4')
            .'<rect x="20" y="20" width="440" height="600" rx="16" fill="#ffffff" stroke="#16a34a" stroke-width="3"/>'
            .'<circle cx="240" cy="110" r="38" fill="#16a34a"/>'
            .'<path d="M222 110l14 14 28-30" stroke="#fff" stroke-width="8" fill="none" stroke-linecap="round" stroke-linejoin="round"/>'
            .'<text x="240" y="200" text-anchor="middle" font-family="sans-serif" font-size="26" font-weight="700" fill="#14532d">Pembayaran Berhasil</text>'
            .'<text x="240" y="260" text-anchor="middle" font-family="sans-serif" font-size="34" font-weight="700" fill="#111827">Rp '.number_format($amount, 0, ',', '.').'</text>'
            .'<text x="240" y="320" text-anchor="middle" font-family="sans-serif" font-size="20" fill="#374151">'.self::e(strtoupper(str_replace('_', ' ', $method))).'</text>'
            .'<text x="240" y="360" text-anchor="middle" font-family="monospace" font-size="18" fill="#374151">'.self::e($code).'</text>'
            .'</svg>';

        return self::write('local', $path, $svg);
    }

    /**
     * Kartu gradien dengan judul, dipakai untuk produk dan kategori.
     */
    private static function card(string $title, string $subtitle, int $variant, int $width, int $height): string
    {
        $hue = (crc32($title) + $variant * 47) % 360;
        $hue2 = ($hue + 40) % 360;
        $lines = array_slice(explode("\n", wordwrap($title, 18, "\n", true)), 0, 4);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$width.' '.$height.'" width="'.$width.'" height="'.$height.'">'
            .'<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1">'
            .'<stop offset="0" stop-color="hsl('.$hue.',65%,62%)"/><stop offset="1" stop-color="hsl('.$hue2.',60%,38%)"/>'
            .'</linearGradient></defs>'
            .'<rect width="100%" height="100%" fill="url(#g)"/>'
            .'<circle cx="'.(120 + $variant * 90).'" cy="140" r="170" fill="#fff" fill-opacity=".10"/>'
            .'<circle cx="680" cy="'.(680 - $variant * 40).'" r="210" fill="#fff" fill-opacity=".08"/>';

        $y = 360 - (count($lines) - 1) * 26;
        foreach ($lines as $line) {
            $svg .= '<text x="400" y="'.$y.'" text-anchor="middle" font-family="sans-serif" font-size="52" font-weight="700" fill="#fff">'.self::e($line).'</text>';
            $y += 62;
        }

        if ($subtitle !== '') {
            $svg .= '<text x="400" y="'.($y + 24).'" text-anchor="middle" font-family="sans-serif" font-size="30" fill="#fff" fill-opacity=".85">'.self::e($subtitle).'</text>';
        }

        return $svg.'</svg>';
    }

    /**
     * Sel penanda sudut (finder pattern) QR: bingkai 7x7 dengan inti 3x3.
     */
    private static function finderCell(int $x, int $y, int $cells): bool
    {
        $ox = $x >= $cells - 7 ? $x - ($cells - 7) : $x;
        $oy = $y >= $cells - 7 ? $y - ($cells - 7) : $y;

        $edge = $ox === 0 || $ox === 6 || $oy === 0 || $oy === 6;
        $core = $ox >= 2 && $ox <= 4 && $oy >= 2 && $oy <= 4;

        return $edge || $core;
    }

    private static function open(int $width, int $height, string $background): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$width.' '.$height.'" width="'.$width.'" height="'.$height.'">'
            .'<rect width="100%" height="100%" fill="'.$background.'"/>';
    }

    private static function write(string $disk, string $path, string $svg): string
    {
        Storage::disk($disk)->put($path, $svg);

        return $path;
    }

    private static function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
