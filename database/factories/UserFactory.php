<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'GUID' => (string) Str::uuid(),
            'NUMBER' => fake()->unique()->numberBetween(1000, 9999),
            'NAME' => fake()->name(),
            'USER_NAME' => fake()->unique()->safeEmail(),
            'PASSWORD' => static::$password ??= Hash::make('password'),
            'USER_LEVEL' => 1,
            'FREEZ' => false,
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            // US000 does not have email_verified_at, so this can be a no-op state
        ]);
    }
}
