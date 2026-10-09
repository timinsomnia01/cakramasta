<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Database\Seeder;

class BannerSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@umart.test')->first();

        $banners = [
            ['Selamat Datang di UM-Mart', 'seed/banners/welcome.jpg'],
            ['Promo Elektronik Kampus', 'seed/banners/elektronik.jpg'],
            ['Kain Batik Khas Malang', 'seed/banners/batik.jpg'],
            ['Jadi Penjual di UM-Mart', 'seed/banners/jadi-penjual.jpg'],
        ];

        foreach ($banners as $i => [$title, $image]) {
            Banner::create([
                'title' => $title,
                'image' => $image,
                'link_url' => '/',
                'sort_order' => $i + 1,
                'starts_at' => now()->startOfDay(),
                'ends_at' => now()->addDays(7)->endOfDay(),
                'created_by' => $admin?->id,
            ]);
        }
    }
}
