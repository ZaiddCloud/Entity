<?php

namespace Tests\Unit\Services;

use App\Models\Category;
use Tests\TestCase;
use App\Services\EntityRelationService;
use App\Models\Book;
use App\Models\Video;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;

class EntityRelationServiceTest extends TestCase
{
    use RefreshDatabase;

    private EntityRelationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EntityRelationService();
        
        // Create user and authenticate to fix user_id null constraint
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);
    }

    /** @test */
    public function it_attaches_tags_to_entity()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);
        $tag1 = Tag::create(['name' => 'PHP']);
        $tag2 = Tag::create(['name' => 'Laravel']);

        $this->service->attachTags($book, [$tag1->id, $tag2->id]);
        $book->refresh();

        $this->assertCount(2, $book->tags);
        $this->assertEqualsCanonicalizing(
            ['PHP', 'Laravel'],
            $book->tags->pluck('name')->toArray()
        );
    }

    /** @test */
    public function it_detaches_tags_from_entity()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);
        $tag1 = Tag::create(['name' => 'PHP']);
        $tag2 = Tag::create(['name' => 'Laravel']);

        $book->tags()->attach([$tag1->id, $tag2->id]);

        $this->service->detachTags($book, [$tag1->id]);
        $book->refresh();

        $this->assertCount(1, $book->tags);
        $this->assertEquals('Laravel', $book->tags->first()->name);
    }

    /** @test */
    public function it_syncs_tags_for_entity()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);
        $tag1 = Tag::create(['name' => 'PHP']);
        $tag2 = Tag::create(['name' => 'Laravel']);
        $tag3 = Tag::create(['name' => 'Database']);

        $book->tags()->attach([$tag1->id, $tag2->id]);

        $this->service->syncTags($book, [$tag2->id, $tag3->id]);
        $book->refresh();

        $this->assertCount(2, $book->tags);
        $this->assertEqualsCanonicalizing(
            ['Database', 'Laravel'],
            $book->tags->pluck('name')->toArray()
        );
    }

    /** @test */
    // tests/Unit/Services/EntityRelationServiceTest.php

    /** @test */
// tests/Unit/Services/EntityRelationServiceTest.php
    /** @test */
    // tests/Unit/Services/EntityRelationServiceTest.php

    /** @test */
    public function it_attaches_categories_to_entity()
    {
        // 1. Arrange
        $book = \App\Models\Book::factory()->create();

        // استخدام CategoryFactory إذا كان موجوداً
        $categories = Category::factory()->count(3)->create();
        $categoryIds = $categories->pluck('id')->toArray();

        // 2. Act
        $this->service->attachCategories($book, $categoryIds);

        // 3. Assert
        $this->assertCount(3, $book->fresh()->categories);
    }
    /** @test */
    public function it_validates_tag_ids_before_attaching()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        $this->expectException(InvalidArgumentException::class);

        // tag ID غير موجود
        $this->service->attachTags($book, [999999]);
    }

    /** @test */
    public function it_attaches_tags_by_name_and_creates_missing_ones()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        $tagIds = $this->service->attachTagsByName($book, ['New Tag 1', 'New Tag 2']);
        $book->refresh();

        $this->assertCount(2, $tagIds);
        $this->assertCount(2, $book->tags);

        $this->assertDatabaseHas('tags', ['name' => 'New Tag 1']);
        $this->assertDatabaseHas('tags', ['name' => 'New Tag 2']);
    }

    /** @test */
    public function it_finds_common_tags_between_entities()
    {
        $book1 = Book::create(['title' => 'Book 1', 'author' => 'Author']);
        $book2 = Book::create(['title' => 'Book 2', 'author' => 'Author']);

        $phpTag = Tag::create(['name' => 'PHP']);
        $laravelTag = Tag::create(['name' => 'Laravel']);
        $jsTag = Tag::create(['name' => 'JavaScript']);

        $book1->tags()->attach([$phpTag->id, $laravelTag->id]);
        $book2->tags()->attach([$phpTag->id, $jsTag->id]);

        // Common tags بين book1 و book2
        $commonTags = $this->service->getCommonTags([$book1, $book2]);

        $this->assertCount(1, $commonTags);

        // التحقق من أن الـ tag المشترك هو PHP
        $tagNames = array_column($commonTags, 'name');
        $this->assertContains('PHP', $tagNames);
    }

    /** @test */
    public function it_copies_tags_from_one_entity_to_another()
    {
        $sourceBook = Book::create(['title' => 'Source Book', 'author' => 'Author']);
        $targetBook = Book::create(['title' => 'Target Book', 'author' => 'Author']);

        $phpTag = Tag::create(['name' => 'PHP']);
        $laravelTag = Tag::create(['name' => 'Laravel']);

        $sourceBook->tags()->attach([$phpTag->id, $laravelTag->id]);

        // نسخ بدون استبدال
        $this->service->copyTags($sourceBook, $targetBook, false);
        $targetBook->refresh();

        $this->assertCount(2, $targetBook->tags);

        $tagNames = $targetBook->tags->pluck('name')->toArray();
        $this->assertContains('PHP', $tagNames);
        $this->assertContains('Laravel', $tagNames);
    }
}
