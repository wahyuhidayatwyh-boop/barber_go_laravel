<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $now = now();

        $products = [
            [
                'name' => 'Pomade Classic',
                'price' => 45000,
                'description' => 'Pomade hold medium untuk tampilan rapi sepanjang hari.',
                'image_url' => 'pomade_classic.jpg',
                'category' => 'Pomade',
                'is_available' => true,
            ],
            [
                'name' => 'Shampoo Refresh',
                'price' => 35000,
                'description' => 'Shampoo pembersih kulit kepala dengan sensasi dingin.',
                'image_url' => 'shampoo_refresh.jpg',
                'category' => 'Shampoo',
                'is_available' => true,
            ],
            [
                'name' => 'Hair Tonic Strong',
                'price' => 55000,
                'description' => 'Tonic perawatan rambut untuk menutrisi akar rambut.',
                'image_url' => 'hair_tonic_strong.jpg',
                'category' => 'Tonic',
                'is_available' => true,
            ],
            [
                'name' => 'Beard Oil Premium',
                'price' => 65000,
                'description' => 'Beard oil untuk menjaga jenggot tetap lembut dan sehat.',
                'image_url' => 'beard_oil_premium.jpg',
                'category' => 'Beard Oil',
                'is_available' => true,
            ],
            [
                'name' => 'Clay Matte',
                'price' => 50000,
                'description' => 'Produk styling hasil matte natural dan mudah dibentuk.',
                'image_url' => 'clay_matte.jpg',
                'category' => 'Lainnya',
                'is_available' => true,
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->updateOrInsert(
                ['name' => $product['name']],
                [
                    'price' => $product['price'],
                    'description' => $product['description'],
                    'image_url' => $product['image_url'],
                    'category' => $product['category'],
                    'is_available' => $product['is_available'],
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );
        }
    }
}

