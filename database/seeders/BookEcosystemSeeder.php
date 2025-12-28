<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Author;
use App\Models\Book;
use App\Models\Publisher;
use App\Models\Topic;
use App\Models\Shelf;
use App\Models\Booker;
use App\Models\Version;

class BookEcosystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create auxiliary data
        $publishers = Publisher::factory()->count(5)->create();
        $authors = Author::factory()->count(10)->create();
        $bookers = Booker::factory()->count(5)->create();
        $topics = Topic::factory()->count(8)->create();
        $shelves = Shelf::factory()->count(3)->create();

        // 2. Create Books with relationships
        $books = Book::factory()->count(20)->create()->each(function ($book) use ($authors, $topics, $bookers) {

            // Attach random authors (1 to 2)
            $book->authors()->attach(
                $authors->random(rand(1, 2))->pluck('id')->toArray()
            );

            // Attach random topics
            $book->topics()->attach(
                $topics->random(rand(1, 3))->pluck('id')->toArray()
            );

            // Attach a translator (Booker) sometimes
            if (rand(0, 1)) {
                $book->bookers()->attach(
                    $bookers->random(1)->pluck('id')->first(),
                    ['role' => 'translator', 'bookable_type' => 'book'] // Polymorphic
                );
            }
        });

        // 3. Create Versions for each book
        $books->each(function ($book) use ($publishers, $shelves) {
            Version::factory()->create([
                'book_id' => $book->id,
                'publisher_id' => $publishers->random()->id,
                'shelf_id' => $shelves->random()->id,
            ]);

            // Sometimes add a second edition version
            if (rand(0, 1)) {
                Version::factory()->create([
                    'book_id' => $book->id,
                    'publisher_id' => $publishers->random()->id,
                    'edition_number' => 2,
                ]);
            }
        });
    }
}
