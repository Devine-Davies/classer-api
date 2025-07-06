<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * Define the model's default state.
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $hasLoggedIn = $this->faker->boolean(80);
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'dob' => fake()->date(),
            'password' => static::$password ??= Hash::make('password'),
            'password_reset_token' => null,
            'email_verification_token' => null,
            'account_status' => $hasLoggedIn ? 1 : 0,
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
