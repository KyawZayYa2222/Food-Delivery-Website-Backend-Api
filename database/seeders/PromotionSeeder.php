<?php

namespace Database\Seeders;

use App\Models\Promotion;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Promotion::insert([
            'promotion_type' => 'giveaway',
            'giveaway_id' => 3,
            'start_date' => '2023-07-16',
            'end_date' => '2023-08-16',
        ]);
    }
}
