<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChild;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

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
            'content_blocks' => [
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
        $this->assertEquals('Chapter 1: The Beginning', $retrieved->content_blocks[0]['title']);
        $this->assertEquals('Hello from MongoDB!', $retrieved->content_blocks[0]['blocks'][0]['content']);
    }

    /** @test */
    public function it_can_update_nested_mongodb_content()
    {
        $content = BookChild::create([
            'book_id' => 'some-id',
            'content_blocks' => [['title' => 'Old Title']]
        ]);

        $content->update([
            'content_blocks' => [['title' => 'New Title']]
        ]);

        $this->assertEquals('New Title', BookChild::find($content->id)->content_blocks[0]['title']);
    }

    /** @test */
    public function a_book_can_access_its_mongodb_children()
    {
        $book = Book::factory()->create();
        
        BookChild::create([
            'book_id' => $book->id,
            'title' => 'Chapter 1'
        ]);

        $this->assertCount(1, $book->children);
        $this->assertEquals('Chapter 1', $book->children->first()->title);
    }

    /** @test */
    public function book_content_service_can_manage_hierarchy()
    {
        $service = new \App\Services\BookContentService();
        $book = Book::factory()->create();

        // Add a Part
        $part = $service->addChild($book, [
            'type' => 'part',
            'title' => 'Part One',
            'order' => 1
        ]);

        // Add a Chapter under that Part
        $chapter = $service->addChild($book, [
            'parent_id' => $part->id,
            'type' => 'chapter',
            'title' => 'Chapter One',
            'order' => 1
        ]);

        $hierarchy = $service->getHierarchy($book);

        $this->assertCount(2, $hierarchy);
        $this->assertEquals('chapter', $hierarchy->where('title', 'Chapter One')->first()->type);
        $this->assertEquals($part->id, $hierarchy->where('title', 'Chapter One')->first()->parent_id);
    }

    /** @test */
    public function it_can_add_annotated_blocks_via_service()
    {
        $service = new \App\Services\BookContentService();
        $child = BookChild::create(['book_id' => '123', 'title' => 'Masala 1']);

        $service->addBlock($child, [
            'body' => 'Main text content',
            'annotations' => [
                ['type' => 'footnote', 'content' => 'Footnote 1']
            ]
        ]);

        $updatedChild = BookChild::find($child->id);
        $this->assertCount(1, $updatedChild->content_blocks);
        $this->assertEquals('Main text content', $updatedChild->content_blocks[0]['body']);
        $this->assertEquals('footnote', $updatedChild->content_blocks[0]['annotations'][0]['type']);
    }

    /** @test */
    public function deleting_a_book_deletes_its_mongodb_children()
    {
        $book = Book::factory()->create();
        
        BookChild::create([
            'book_id' => $book->id,
            'title' => 'Chapter to be deleted'
        ]);

        $this->assertCount(1, BookChild::where('book_id', $book->id)->get());

        // Delete the MySQL book
        $book->delete();

        // Check if MongoDB children are gone
        $this->assertCount(0, BookChild::where('book_id', $book->id)->get());
    }
}
