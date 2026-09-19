<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed data dasar GAK CRM (produk + user).
     * Aman dijalankan berulang — pakai updateOrCreate, tidak menggandakan.
     *
     * Jalankan: php artisan db:seed
     */
    public function run(): void
    {
        $this->call([
            ProductSeeder::class,
            UserSeeder::class,
        ]);
    }
}
