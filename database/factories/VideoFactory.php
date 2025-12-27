<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Video>
 */
class VideoFactory extends Factory
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
            'duration' => $this->faker->numberBetween(60, 7200), // seconds
            'format' => $this->faker->randomElement(['mp4', 'mkv', 'avi']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
