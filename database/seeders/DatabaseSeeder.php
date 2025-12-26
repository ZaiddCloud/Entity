<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Activity;
use App\Models\Audio;
use App\Models\Book;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Comment;
use App\Models\Manuscript;
use App\Models\Note;
use App\Models\Series;
use App\Models\Tag;
use App\Models\Video;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->call([
            ActivitySeeder::class,
            AudioSeeder::class,
            BookSeeder::class,
            CategorySeeder::class,
            CollectionSeeder::class,
            CommentSeeder::class,
            ManuscriptSeeder::class,
            NoteSeeder::class,
            SeriesSeeder::class,
            TagSeeder::class,
            VideoSeeder::class,
        ]);
    }
}
