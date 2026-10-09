<?php

namespace Database\Seeders;

use App\Models\VisitorLog;
use Illuminate\Database\Seeder;

class VisitorLogSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];

        foreach (range(0, 13) as $daysAgo) {
            $date = now()->subDays($daysAgo);

            foreach (range(1, rand(10, 40)) as $_) {
                $rows[] = [
                    'user_id' => null,
                    'ip_address' => rand(36, 180).'.'.rand(0, 255).'.'.rand(0, 255).'.'.rand(1, 254),
                    'user_agent' => 'Seeder/1.0',
                    'path' => collect(['/', '/kategori/elektronik', '/kategori/kain', '/kategori/pakaian-pria'])->random(),
                    'visited_on' => $date->toDateString(),
                    'created_at' => $date,
                ];
            }
        }

        foreach (array_chunk($rows, 200) as $chunk) {
            VisitorLog::insert($chunk);
        }
    }
}
