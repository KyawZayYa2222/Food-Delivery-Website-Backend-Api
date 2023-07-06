<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::pluck('id')->random();

        $productId = Product::pluck('id')->random();

        return [
            'user_id' => $userId,
            'product_id' => $productId,
            'product_count' => fake()->numberBetween(1, 10),
            'location' => fake()->address(),
            'total_cost' => fake()->numberBetween(10, 1000),
        ];
    }
}
