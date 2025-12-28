<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Shelf;

class ShelfFactory extends Factory
{
    protected $model = Shelf::class;

    public function definition(): array
    {
        return [
            'location_code' => $this->faker->bothify('?##-?#'),
            'capacity' => $this->faker->numberBetween(10, 100),
        ];
    }
}
