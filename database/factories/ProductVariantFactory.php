<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $size = ['S', 'M', 'L', 'XL', 'XXL', 'XXXL'];
        $color = ['Red', 'Green', 'Blue', 'Yellow', 'Purple', 'White', 'Black'];
        return [
            'color' => Arr::random($color),
            'size' => Arr::random($size),
            'price' => fake()->numberBetween(10000, 100000)
        ];
    }
}
