<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $tree = [
            'Elektronik' => [
                'Konsol Game', 'Aksesoris Konsol', 'Alat Casting', 'Foot Bath & Spa',
                'Mesin Jahit & Aksesoris', 'Setrika & Mesin Uap', 'Purifier & Humidifier',
                'Penyedot Debu & Peralatan Perawatan Lantai', 'Telepon', 'Mesin Cuci & Pengering',
                'Water Heater', 'Pendingin Ruangan', 'Pengering Sepatu', 'Penghangat Ruangan',
                'TV & Aksesoris', 'Perangkat Dapur', 'Lampu & Pencahayaan',
            ],
            'Komputer & Aksesoris' => ['Laptop', 'Keyboard & Mouse', 'Monitor', 'Penyimpanan Data', 'Printer & Scanner'],
            'Handphone & Aksesoris' => ['Handphone', 'Casing & Pelindung', 'Charger & Kabel', 'Powerbank', 'Headset & Earphone'],
            'Pakaian Pria' => ['Kaos Pria', 'Kemeja Pria', 'Celana Pria', 'Jaket Pria'],
            'Sepatu Pria' => ['Sneakers Pria', 'Sepatu Formal Pria', 'Sandal Pria'],
            'Tas Pria' => ['Ransel Pria', 'Tas Selempang Pria', 'Dompet Pria'],
            'Aksesoris Fashion' => ['Kacamata', 'Topi', 'Ikat Pinggang', 'Syal & Selendang'],
            'Jam Tangan' => ['Jam Tangan Pria', 'Jam Tangan Wanita', 'Smartwatch'],
            'Kesehatan' => ['Masker & Alat Pelindung', 'Vitamin & Suplemen', 'Alat Kesehatan'],
            'Hobi & Koleksi' => ['Alat Musik', 'Mainan & Koleksi', 'Alat Tulis & Seni'],
            'Makanan & Minuman' => ['Makanan Ringan', 'Minuman', 'Makanan Instan', 'Bahan Masakan'],
            'Perawatan & Kecantikan' => ['Perawatan Wajah', 'Perawatan Rambut', 'Make Up'],
            'Perlengkapan Rumah' => ['Perabot', 'Dekorasi Rumah', 'Peralatan Dapur', 'Peralatan Kebersihan'],
            'Pakaian Wanita' => ['Atasan Wanita', 'Dress', 'Rok', 'Celana Wanita'],
            'Fashion Muslim' => ['Gamis', 'Hijab & Kerudung', 'Baju Koko', 'Mukena'],
            'Fashion Bayi & Anak' => ['Pakaian Bayi', 'Pakaian Anak', 'Sepatu Anak'],
            'Ibu & Bayi' => ['Perlengkapan Menyusui', 'Perlengkapan Mandi Bayi', 'Mainan Bayi'],
            'Sepatu Wanita' => ['Flat Shoes', 'Sneakers Wanita', 'Sandal Wanita', 'Heels'],
            'Tas Wanita' => ['Tas Tangan', 'Ransel Wanita', 'Tas Selempang Wanita', 'Dompet Wanita'],
            'Otomotif' => ['Helm', 'Aksesoris Motor', 'Aksesoris Mobil', 'Oli & Perawatan'],
            // contoh route /kategori/kain pada dokumen requirement
            'Kain' => ['Kain Batik', 'Kain Katun', 'Kain Satin', 'Kain Tenun'],
        ];

        $order = 1;

        foreach ($tree as $parentName => $children) {
            $parent = Category::create([
                'name' => $parentName,
                'slug' => $this->uniqueSlug($parentName),
                'icon' => 'seed/categories/'.Str::slug($parentName).'.png',
                'sort_order' => $order++,
            ]);

            foreach ($children as $i => $childName) {
                Category::create([
                    'parent_id' => $parent->id,
                    'name' => $childName,
                    'slug' => $this->uniqueSlug($childName, $parentName),
                    'sort_order' => $i + 1,
                ]);
            }
        }
    }

    private function uniqueSlug(string $name, ?string $parent = null): string
    {
        $slug = Str::slug($name);

        if (Category::where('slug', $slug)->exists()) {
            $slug = Str::slug(trim($parent.' '.$name));
        }

        return $slug;
    }
}
