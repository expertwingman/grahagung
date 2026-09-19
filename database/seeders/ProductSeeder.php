<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            'Wisata Semanggi',
            'Semanggi Mangrove',
            'Blukid Residence 3',
            'Wisata Bukit Sentul',
            'Grand Semanggi Residence',
        ];

        foreach ($products as $name) {
            // updateOrCreate → aman dijalankan berulang, tidak menggandakan
            Product::updateOrCreate(
                ['name' => $name],
                ['is_active' => true]
            );
        }

        $this->command->info('Produk: ' . count($products) . ' tersedia.');
    }
}
