<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChild;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookChildTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_book_child_in_mongodb()
    {
        // 1. Create a Book in MySQL (Metadata)
        $book = Book::factory()->create([
            'title' => 'Test Book for Mongo'
        ]);

        // 2. Create Content in MongoDB
        $content = BookChild::create([
            'book_id' => $book->id,
            'language' => 'ar',
            'chapters' => [
                [
                    'title' => 'Chapter 1: The Beginning',
                    'blocks' => [
                        ['type' => 'text', 'content' => 'Hello from MongoDB!']
                    ]
                ]
            ]
        ]);

        // 3. Assertions
        $this->assertNotNull($content->id);
        $this->assertEquals($book->id, $content->book_id);
        
        // Find it back from MongoDB
        $retrieved = BookChild::find($content->id);
        $this->assertEquals('Chapter 1: The Beginning', $retrieved->chapters[0]['title']);
        $this->assertEquals('Hello from MongoDB!', $retrieved->chapters[0]['blocks'][0]['content']);
    }

    /** @test */
    public function it_can_update_nested_mongodb_content()
    {
        $content = BookChild::create([
            'book_id' => 'some-id',
            'chapters' => [['title' => 'Old Title']]
        ]);

        $content->update([
            'chapters' => [['title' => 'New Title']]
        ]);

        $this->assertEquals('New Title', BookChild::find($content->id)->chapters[0]['title']);
    }

    /** @test */
    public function it_can_delete_mongodb_content()
    {
        $content = BookChild::create(['book_id' => 'delete-me']);
        $id = $content->id;

        $content->delete();

        $this->assertNull(BookChild::find($id));
    }
}
