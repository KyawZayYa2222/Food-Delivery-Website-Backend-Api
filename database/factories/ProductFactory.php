<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categoryId = Category::pluck('id')->random();

        return [
            'name' => fake()->name(),
            'price' => fake()->numberBetween(10, 1000),
            'short_desc' => fake()->text,
            'long_desc' => fake()->text,
            'image' => fake()->imageUrl(),
            'category_id' => $categoryId,
        ];
    }
}
