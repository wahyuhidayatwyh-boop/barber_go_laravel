<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BannerSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $banners = [
            [
                'title' => 'Promo Haircut Hemat',
                'description' => 'Diskon 20% untuk layanan Haircut Basic minggu ini.',
                'image_url' => 'promo_haircut.jpg',
                'is_active' => true,
            ],
            [
                'title' => 'Paket Full Service',
                'description' => 'Ambil paket lengkap, tampil makin fresh dan rapi.',
                'image_url' => 'promo_full_service.jpg',
                'is_active' => true,
            ],
        ];

        foreach ($banners as $banner) {
            DB::table('banners')->updateOrInsert(
                ['title' => $banner['title']],
                [
                    'description' => $banner['description'],
                    'image_url' => $banner['image_url'],
                    'is_active' => $banner['is_active'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}

