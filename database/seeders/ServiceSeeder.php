<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $services = [
            [
                'name' => 'Haircut Basic',
                'price' => 30000,
                'description' => 'Potong rambut standar sesuai request pelanggan',
                'image_url' => 'haircut_basic.jpg',
            ],
            [
                'name' => 'Haircut + Wash',
                'price' => 40000,
                'description' => 'Potong rambut dilengkapi dengan keramas',
                'image_url' => 'haircut_wash.jpg',
            ],
            [
                'name' => 'Haircut + Styling',
                'price' => 50000,
                'description' => 'Potong rambut dan penataan menggunakan produk styling',
                'image_url' => 'haircut_styling.jpg',
            ],
            [
                'name' => 'Beard Trim',
                'price' => 25000,
                'description' => 'Perapihan dan pemangkasan jenggot',
                'image_url' => 'beard_trim.jpg',
            ],
            [
                'name' => 'Full Service Package',
                'price' => 70000,
                'description' => 'Potong rambut, keramas, pijat ringan, dan styling',
                'image_url' => 'full_service.jpg',
            ],
        ];

        foreach ($services as $service) {
            DB::table('services')->updateOrInsert(
                ['name' => $service['name']],
                [
                    'price' => $service['price'],
                    'description' => $service['description'],
                    'image_url' => $service['image_url'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}

