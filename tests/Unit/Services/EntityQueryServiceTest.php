<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EntityQueryService;
use App\Models\Book;
use App\Models\Video;
use App\Models\Tag;
use App\Models\User;
use App\Models\Assignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class EntityQueryServiceTest extends TestCase
{
    use RefreshDatabase;

    private EntityQueryService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EntityQueryService();

        // إنشاء بيانات اختبارية
        Book::create(['title' => 'Laravel Book', 'author' => 'Author 1']);
        Book::create(['title' => 'PHP Advanced', 'author' => 'Author 2']);
        Video::create(['title' => 'Laravel Tutorial', 'duration' => 3600]);
        Video::create(['title' => 'PHP Basics', 'duration' => 1800]);

        // إضافة tags
        $phpTag = Tag::create(['name' => 'PHP']);
        $laravelTag = Tag::create(['name' => 'Laravel']);

        Book::first()->tags()->attach($phpTag);
        Video::first()->tags()->attach($laravelTag);
    }

    #[Test]
    public function service_can_be_instantiated()
    {
        $this->assertInstanceOf(EntityQueryService::class, $this->service);
    }

    #[Test]
    public function it_searches_across_all_entities()
    {
        $results = $this->service->search('Laravel');

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('title', 'Laravel Book'));
        $this->assertTrue($results->contains('title', 'Laravel Tutorial'));
    }

    #[Test]
    public function it_filters_by_entity_type()
    {
        $books = $this->service->filter(['type' => 'book']);

        $this->assertCount(2, $books);
        $this->assertContainsOnlyInstancesOf(Book::class, $books);
    }

    #[Test]
    public function it_filters_by_multiple_criteria()
    {
        $results = $this->service->filter([
            'type' => 'book',
            'search' => 'PHP'
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('PHP Advanced', $results->first()->title);
    }

    #[Test]
    public function it_paginates_results()
    {
        $paginator = $this->service->paginate(1);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertEquals(1, $paginator->perPage());
        $this->assertEquals(4, $paginator->total()); // جميع الـ entities
    }

    #[Test]
    public function it_searches_by_tag()
    {
        $results = $this->service->searchByTag('PHP');

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Book', $results->first()->title);
    }

    #[Test]
    public function it_gets_recent_entities()
    {
        $recent = $this->service->recent(7); // آخر 7 أيام

        $this->assertCount(4, $recent); // جميعها حديثة
    }

    #[Test]
    public function it_gets_popular_entities()
    {
        $popularBook = Book::first();
        // إنشاء أنشطة لمحاكاة الشعبية
        for ($i = 0; $i < 5; $i++) {
            $popularBook->activities()->create([
                'activity_type' => 'view',
                'description' => 'Viewed'
            ]);
        }

        $popular = $this->service->popular(1);

        $this->assertCount(1, $popular);
        $this->assertEquals($popularBook->title, $popular->first()->title);
    }

    #[Test]
    public function it_returns_only_user_assigned_ids_for_regular_user()
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $otherUser = User::factory()->create();
        $book = Book::first();
        $otherBook = Book::all()->last();

        Assignment::create([
            'user_id' => $user->id,
            'entity_type' => Book::class,
            'entity_id' => $book->id,
            'status' => 'pending'
        ]);

        Assignment::create([
            'user_id' => $otherUser->id,
            'entity_type' => Book::class,
            'entity_id' => $otherBook->id,
            'status' => 'pending'
        ]);

        $ids = $this->service->getAssignedEntityIds($user, Book::class);

        $this->assertCount(1, $ids);
        $this->assertEquals($book->id, $ids[0]);
    }

    #[Test]
    public function it_returns_all_assigned_ids_for_admin()
    {
        $admin = User::factory()->create(['email' => 'admin@admin.com']);
        $user = User::factory()->create();
        $book = Book::first();
        $otherBook = Book::all()->last();

        Assignment::create([
            'user_id' => $user->id,
            'entity_type' => Book::class,
            'entity_id' => $book->id,
            'status' => 'pending'
        ]);

        Assignment::create([
            'user_id' => $admin->id,
            'entity_type' => Book::class,
            'entity_id' => $otherBook->id,
            'status' => 'pending'
        ]);

        $ids = $this->service->getAssignedEntityIds($admin, Book::class);

        // Admin sees both
        $this->assertCount(2, $ids);
        $this->assertContains($book->id, $ids);
        $this->assertContains($otherBook->id, $ids);
    }

    #[Test]
    public function it_returns_empty_array_on_failure()
    {
        // We pass a string that isn't a class to trigger a potential error or just return empty
        // The implementation has a try-catch for broader safety
        $user = User::factory()->create();
        
        $ids = $this->service->getAssignedEntityIds($user, 'NonExistentClass');
        
        $this->assertIsArray($ids);
        $this->assertEmpty($ids);
    }
}
