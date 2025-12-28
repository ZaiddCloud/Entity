<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BookRelationsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function book_has_versions_relationship()
    {
        $book = Book::factory()->create();

        $this->assertTrue(method_exists($book, 'versions'), 'Book model is missing versions() relationship');
        $this->assertInstanceOf(HasMany::class, $book->versions());
    }

    /** @test */
    public function book_has_authors_relationship()
    {
        $book = Book::factory()->create();

        $this->assertTrue(method_exists($book, 'authors'), 'Book model is missing authors() relationship');
        $this->assertInstanceOf(BelongsToMany::class, $book->authors());
    }

    /** @test */
    public function book_has_bookers_polymorphic_relationship()
    {
        $book = Book::factory()->create();

        $this->assertTrue(method_exists($book, 'bookers'), 'Book model is missing bookers() relationship');
        $this->assertInstanceOf(MorphToMany::class, $book->bookers());
    }

    /** @test */
    public function book_has_topics_relationship()
    {
        $book = Book::factory()->create();

        $this->assertTrue(method_exists($book, 'topics'), 'Book model is missing topics() relationship');
        $this->assertInstanceOf(BelongsToMany::class, $book->topics());
    }
}
