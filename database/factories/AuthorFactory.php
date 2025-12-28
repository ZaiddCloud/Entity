<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Author;

class AuthorFactory extends Factory
{
    protected $model = Author::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'slug' => $this->faker->unique()->slug(),
            'bio' => $this->faker->paragraph(),
            'birth_year' => $this->faker->numberBetween(1800, 1980),
            'death_year' => $this->faker->optional()->numberBetween(1900, 2025),
        ];
    }
}
