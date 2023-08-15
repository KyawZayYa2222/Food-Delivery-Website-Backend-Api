<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Database\Seeders\CartSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\OrderSeeder;
use Database\Seeders\ContactSeeder;
use Database\Seeders\ProductSeeder;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SlideshowSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(UserSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CartSeeder::class);
        $this->call(OrderSeeder::class);
        $this->call(ContactSeeder::class);
        $this->call(SlideshowSeeder::class);
    }
}
