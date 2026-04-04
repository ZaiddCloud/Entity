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
            'original_title' => $this->faker->optional(0.7)->realText(25),
            'code' => $this->faker->optional(0.5)->uuid(),
            'manuscript_century' => (string) $this->faker->numberBetween(2, 14),
            'manuscript_century_label' => $this->faker->numberBetween(2, 14) . ' هـ',
            
            // Physical Metadata
            'catalog_number' => $this->faker->bothify('MS-####-??'),
            'scribe' => $this->faker->name(),
            'copy_date' => $this->faker->year() . ' هـ',
            'parts' => (string) $this->faker->numberBetween(1, 10),
            'script_type' => $this->faker->randomElement(['نسخ', 'كوفي', 'ديواني', 'رقعة']),
            'dimensions' => $this->faker->numberBetween(15, 30) . 'x' . $this->faker->numberBetween(10, 20) . ' سم',
            'lines_per_page' => $this->faker->numberBetween(15, 30),
            'inscriptions' => $this->faker->realText(100),
            'notes' => $this->faker->realText(50),
            
            'pages' => $this->faker->numberBetween(50, 1000),
            'location' => $this->faker->city(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
