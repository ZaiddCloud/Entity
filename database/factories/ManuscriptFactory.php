<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Manuscript>
 */
class ManuscriptFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->realText(20);
        return [
            'title' => $title,
            'author' => $this->faker->name(),
            'century' => $this->faker->numberBetween(10, 19),
            'language' => $this->faker->languageCode(),
            'pages' => $this->faker->numberBetween(50, 1000),
            'location' => $this->faker->city(),
            'publisher' => $this->faker->company(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
