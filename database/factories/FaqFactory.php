<?php

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => rtrim($this->faker->sentence(), '.').'?',
            'answer' => $this->faker->paragraph(),
            'category' => $this->faker->randomElement(['Getting Started', 'Storage', 'Compatibility', 'Support']),
            'sort_order' => $this->faker->numberBetween(0, 50),
            'is_published' => true,
        ];
    }

    /**
     * Indicate that the FAQ is unpublished.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
