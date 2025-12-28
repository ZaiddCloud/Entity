<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Version;
use App\Models\Book;
use App\Models\Publisher;

class VersionFactory extends Factory
{
    protected $model = Version::class;

    public function definition(): array
    {
        return [
            'book_id' => Book::factory(),
            'publisher_id' => Publisher::factory(),
            'file_path' => 'books/' . $this->faker->slug() . '.pdf',
            'isbn' => $this->faker->isbn13(),
            'pages' => $this->faker->numberBetween(100, 1000),
            'published_year' => $this->faker->year(),
            'edition_number' => $this->faker->numberBetween(1, 5),
            'format' => 'pdf',
            'file_size' => $this->faker->numberBetween(1024000, 52428800),
        ];
    }
}
