<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Book;
use App\Models\EntityContent;
use App\Services\EntityContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class UnifiedContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Ensure MongoDB collection is clean before tests if needed
        if (class_exists(EntityContent::class)) {
            EntityContent::truncate();
        }
    }

    /** @test */
    public function it_can_create_unified_content_for_different_entities()
    {
        // 1. Create Entities (MySQL)
        $book = Book::factory()->create(['title' => 'Unified Book']);
        $audio = Audio::factory()->create(['title' => 'Unified Audio']);

        // 2. Create Content via Model directly (Low Level)
        $bookContent = EntityContent::create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'type' => 'chapter',
            'title' => 'Chapter 1',
            'content_blocks' => [['text' => 'Book text']]
        ]);

        $audioContent = EntityContent::create([
            'entity_id' => $audio->id,
            'entity_type' => 'audio',
            'type' => 'segment',
            'title' => 'Segment 1',
            'metadata' => ['start' => 0, 'end' => 60]
        ]);

        // 3. Assertions
        $this->assertNotNull($bookContent->id);
        $this->assertEquals('book', $bookContent->entity_type);

        $this->assertNotNull($audioContent->id);
        $this->assertEquals('audio', $audioContent->entity_type);
    }

    /** @test */
    public function service_enforces_allowed_content_types()
    {
        $service = new EntityContentService();
        $book = Book::factory()->create();
        $audio = Audio::factory()->create();

        // 1. Valid Book Content (Chapter) -> Should Pass
        $chapter = $service->createNode($book, [
            'type' => 'chapter',
            'title' => 'Valid Chapter'
        ]);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Model::class, $chapter);

        // 2. Invalid Book Content (Segment) -> Should Fail
        $this->expectException(ValidationException::class);
        $service->createNode($book, [
            'type' => 'segment', // Segments are for Audio/Video
            'title' => 'Invalid Segment'
        ]);
    }

    /** @test */
    public function entities_can_access_their_content_via_unified_relation()
    {
        $book = Book::factory()->create();

        EntityContent::create([
            'entity_id' => $book->id,
            'entity_type' => 'book', // Important: The relation will filter by this
            'title' => 'Content #1'
        ]);

        // Verify the 'contents' relation works
        // Note: usage of 'contents' implies we added the trait or method to Entity
        $this->assertTrue(method_exists($book, 'contents'), 'Entity must have contents relation');
        $this->assertCount(1, $book->contents);
        $this->assertEquals('Content #1', $book->contents->first()->title);
    }
}
