<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Deletion>
 */
class DeletionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entity_id' => \App\Models\Book::factory(),
            'entity_type' => 'book',
            'reason' => $this->faker->sentence(),
            'user_id' => \App\Models\User::factory(),
            'deleted_at' => now(),
        ];
    }
}
