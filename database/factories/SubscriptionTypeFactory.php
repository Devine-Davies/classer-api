<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class SubscriptionTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'label' => 'License ' . $this->faker->word(),
            'code' => $this->faker->regexify('[A-Z0-9]{5}'),
            'type' => 'Full',
            'limit_short_count' => 100,
            'limit_short_duration' => 30,
            'limit_short_size' => 2097152,
        ];
    }
}