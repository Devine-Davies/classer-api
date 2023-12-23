<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class SubscriptionFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => 1,
            'issue_date' => $this->faker->dateTime('now'),
            'expiration_date' => $this->faker->dateTimeBetween('now', '+1 years'),
        ];
    }

    // Take the first 3 - 6 characters of the uuid
    public function shortUuid(): string
    {
        return substr($this->faker->uuid(), 0, strrpos($this->faker->uuid(), '-'));
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}