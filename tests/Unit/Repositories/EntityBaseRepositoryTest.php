<?php

namespace Tests\Unit\Repositories;

use Tests\TestCase;
use App\Repositories\EntityBaseRepository;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use PHPUnit\Framework\Attributes\Test;

class EntityBaseRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private EntityBaseRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new EntityBaseRepository();
    }

    #[Test]
    public function repository_can_be_instantiated()
    {
        $this->assertInstanceOf(EntityBaseRepository::class, $this->repository);
    }

    #[Test]
    public function it_finds_entity_by_id()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        $found = $this->repository->find($book->id);

        $this->assertNotNull($found);
        $this->assertInstanceOf(Book::class, $found);
        $this->assertEquals($book->id, $found->id);
        $this->assertEquals('Test Book', $found->title);
    }

    #[Test]
    public function it_returns_null_when_entity_not_found_by_id()
    {
        $found = $this->repository->find(999999);

        $this->assertNull($found);
    }

    #[Test]
    public function it_finds_entity_by_slug()
    {
        $book = Book::create([
            'title' => 'Laravel Book',
            'author' => 'Author',
            'slug' => 'laravel-book'
        ]);

        $found = $this->repository->findBySlug('laravel-book');

        $this->assertInstanceOf(Book::class, $found);
        $this->assertEquals($book->id, $found->id);
        $this->assertEquals('Laravel Book', $found->title);
    }

    #[Test]
    public function it_returns_null_when_entity_not_found_by_slug()
    {
        $found = $this->repository->findBySlug('non-existent-slug');

        $this->assertNull($found);
    }

    #[Test]
    public function it_finds_entity_by_type_and_id()
    {
        $book = Book::create(['title' => 'Book 1', 'author' => 'Author']);
        $video = Video::create(['title' => 'Video 1', 'duration' => 300]);

        $foundBook = $this->repository->findByType(Book::class, $book->id);
        $foundVideo = $this->repository->findByType(Video::class, $video->id);

        $this->assertInstanceOf(Book::class, $foundBook);
        $this->assertInstanceOf(Video::class, $foundVideo);
        $this->assertEquals($book->id, $foundBook->id);
        $this->assertEquals($video->id, $foundVideo->id);
    }

    #[Test]
    public function it_finds_entity_with_relations()
    {
        $book = Book::create(['title' => 'Book with Tags', 'author' => 'Author']);
        $tag = Tag::create(['name' => 'PHP']);
        $book->tags()->attach($tag);

        $found = $this->repository->findWithRelations($book->id, ['tags']);

        $this->assertTrue($found->relationLoaded('tags'));
        $this->assertCount(1, $found->tags);
        $this->assertEquals('PHP', $found->tags->first()->name);
    }

    #[Test]
    public function it_returns_all_entities()
    {
        Book::create(['title' => 'Book 1', 'author' => 'Author 1']);
        Book::create(['title' => 'Book 2', 'author' => 'Author 2']);
        Video::create(['title' => 'Video 1', 'duration' => 300]);

        $all = $this->repository->all();

        $this->assertCount(3, $all);

        $bookCount = $all->filter(fn($e) => $e instanceof Book)->count();
        $videoCount = $all->filter(fn($e) => $e instanceof Video)->count();

        $this->assertEquals(2, $bookCount);
        $this->assertEquals(1, $videoCount);
    }

    #[Test]
    public function it_filters_entities_by_type()
    {
        Book::create(['title' => 'Book 1', 'author' => 'Author']);
        Book::create(['title' => 'Book 2', 'author' => 'Author']);
        Video::create(['title' => 'Video 1', 'duration' => 300]);
        Video::create(['title' => 'Video 2', 'duration' => 400]);

        $books = $this->repository->all(['type' => 'book']);
        $videos = $this->repository->all(['type' => 'video']);

        $this->assertCount(2, $books);
        $this->assertCount(2, $videos);

        $this->assertContainsOnlyInstancesOf(Book::class, $books);
        $this->assertContainsOnlyInstancesOf(Video::class, $videos);
    }

    #[Test]
    public function it_paginates_entities()
    {
        // إنشاء 15 entity
        for ($i = 1; $i <= 15; $i++) {
            Book::create(['title' => "Book $i", 'author' => "Author $i"]);
        }

        $paginator = $this->repository->paginate(10);

        $this->assertInstanceOf(LengthAwarePaginator::class, $paginator);
        $this->assertEquals(10, $paginator->perPage());
        $this->assertEquals(15, $paginator->total());
        $this->assertEquals(2, $paginator->lastPage());
        $this->assertCount(10, $paginator->items());
    }

    #[Test]
    public function it_creates_entity()
    {
        $data = [
            'type' => 'book',
            'title' => 'New Book',
            'author' => 'New Author',
            'isbn' => '1234567890123'
        ];

        $entity = $this->repository->create($data);

        $this->assertInstanceOf(Book::class, $entity);
        $this->assertEquals('New Book', $entity->title);
        $this->assertEquals('New Author', $entity->author);
        $this->assertDatabaseHas('books', [
            'title' => 'New Book',
            'author' => 'New Author'
        ]);
    }

    #[Test]
    public function it_updates_entity()
    {
        $book = Book::create(['title' => 'Old Title', 'author' => 'Old Author']);

        $updated = $this->repository->update($book, [
            'title' => 'Updated Title',
            'author' => 'Updated Author'
        ]);

        $this->assertTrue($updated);

        $book->refresh();
        $this->assertEquals('Updated Title', $book->title);
        $this->assertEquals('Updated Author', $book->author);
        $this->assertEquals('updated-title', $book->slug);
    }

    #[Test]
    public function it_deletes_entity()
    {
        $book = Book::create(['title' => 'To Delete', 'author' => 'Author']);

        $deleted = $this->repository->delete($book);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($book);
    }

    #[Test]
    public function it_restores_deleted_entity()
    {
        $book = Book::create(['title' => 'To Restore', 'author' => 'Author']);
        $book->delete();

        $restored = $this->repository->restore($book);

        $this->assertTrue($restored);
        $this->assertFalse($book->fresh()->trashed());
    }
}
