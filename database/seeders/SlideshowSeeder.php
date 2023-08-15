<?php

namespace Database\Seeders;

use App\Models\Slideshow;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SlideshowSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Slideshow::insert([
            'name' => 'slideshow01',
            'title' => 'slideshow title',
            'sub_title' => 'slideshow sub title',
            'description' => 'slideshow description',
            'image' => 'slideshow image',
            'show_date' => '2023-07-27',
            'end_date' => '2023-08-20',
        ]);
    }
}
