<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Audio>
 */
class AudioFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->unique()->company();
        return [
            'title' => $title,
            'duration' => $this->faker->numberBetween(180, 3600),
            'format' => $this->faker->randomElement(['mp3', 'wav', 'aac']),
            'bitrate' => $this->faker->randomElement([128, 192, 320]),
            'sample_rate' => $this->faker->randomElement([44100, 48000]),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
