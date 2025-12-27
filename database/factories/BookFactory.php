<?php

namespace Database\Factories;

use App\Models\Book;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->realText(20);
        return [
            'title' => $title,
            'author' => $this->faker->name(),
            'isbn' => $this->faker->isbn13(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
