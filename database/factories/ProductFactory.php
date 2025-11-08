<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Product;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Product::class;
    
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => fake()->randomNumber(8, true),
            'title' => fake()->sentence(),
            'description' => fake()->text(),
            'style' => '054X',
            'mainframe_color' => fake()->colorName(),
            'size' => 'XXXL',
            'color' => fake()->colorName(),
            'price' => fake()->randomFloat(2, 10, 100)
        ];
    }
}
