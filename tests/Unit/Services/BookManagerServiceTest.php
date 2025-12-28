<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Author;
use App\Models\Publisher;
use App\Services\BookManagerService; // This service needs to be updated/refactored
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_book_with_initial_version_and_authors()
    {
        // 1. Arrange: Use Factories
        $author = Author::factory()->create();
        $publisher = Publisher::factory()->create();

        $data = [
            'title' => 'The Factory Book',
            'type' => 'book',
            'author_ids' => [$author->id],
            'publisher_id' => $publisher->id,
            'file_path' => 'books/factory_book.pdf',
            'isbn' => '978-1234567890',
            'pages' => 300,
        ];

        // 2. Act
        $service = app(BookManagerService::class);
        $book = $service->createBook($data);

        // 3. Assert
        $this->assertInstanceOf(Book::class, $book);
        $this->assertEquals('The Factory Book', $book->title);

        // Assert Author attached
        $this->assertTrue($book->authors->contains($author));

        // Assert Version created
        $this->assertCount(1, $book->versions);
        $version = $book->versions->first();
        $this->assertEquals('books/factory_book.pdf', $version->file_path);
        // $this->assertEquals('978-1234567890', $version->isbn); // Optional strict check
        $this->assertEquals($publisher->id, $version->publisher_id);
    }

    /** @test */
    public function it_updates_book_and_relations()
    {
        // 1. Arrange: Create initial book with author
        $author = Author::factory()->create();
        $book = Book::factory()->create(['title' => 'Old Title']);
        $book->authors()->attach($author);
        // Create initial version
        \App\Models\Version::factory()->create([
            'book_id' => $book->id,
            'pages' => 100
        ]);

        $newAuthor = Author::factory()->create();
        $newPublisher = Publisher::factory()->create();

        $updateData = [
            'title' => 'Updated Title',
            'description' => 'New Description',
            'author_ids' => [$newAuthor->id], // Switch author
            'publisher_id' => $newPublisher->id,
            'pages' => 500,
        ];

        // 2. Act
        $service = app(BookManagerService::class);
        $updatedBook = $service->updateBook($book, $updateData);

        // 3. Assert
        $this->assertEquals('Updated Title', $updatedBook->fresh()->title);
        $this->assertEquals('New Description', $updatedBook->fresh()->description);

        // Assert Author Synced
        $this->assertCount(1, $updatedBook->authors);
        $this->assertEquals($newAuthor->id, $updatedBook->authors->first()->id);

        // Assert Version Updated (Assuming created lazily or updated)
        // Since factory creates book without version usually, updateBook logic creates/updates it.
        // Let's ensure version exists
        $version = $updatedBook->versions->first();
        $this->assertNotNull($version);
        $this->assertEquals($newPublisher->id, $version->publisher_id);
        $this->assertEquals(500, $version->pages);
    }
}
