<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EntityQueryService;
use App\Models\Book;
use App\Models\Video;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;

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

    /** @test */
    public function service_can_be_instantiated()
    {
        $this->assertInstanceOf(EntityQueryService::class, $this->service);
    }

    /** @test */
    public function it_searches_across_all_entities()
    {
        $results = $this->service->search('Laravel');

        $this->assertCount(2, $results);
        $this->assertTrue($results->contains('title', 'Laravel Book'));
        $this->assertTrue($results->contains('title', 'Laravel Tutorial'));
    }

    /** @test */
    public function it_filters_by_entity_type()
    {
        $books = $this->service->filter(['type' => 'book']);

        $this->assertCount(2, $books);
        $this->assertContainsOnlyInstancesOf(Book::class, $books);
    }

    /** @test */
    public function it_filters_by_multiple_criteria()
    {
        $results = $this->service->filter([
            'type' => 'book',
            'search' => 'PHP'
        ]);

        $this->assertCount(1, $results);
        $this->assertEquals('PHP Advanced', $results->first()->title);
    }

    /** @test */
    public function it_paginates_results()
    {
        $paginator = $this->service->paginate(1);

        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $paginator);
        $this->assertEquals(1, $paginator->perPage());
        $this->assertEquals(4, $paginator->total()); // جميع الـ entities
    }

    /** @test */
    public function it_searches_by_tag()
    {
        $results = $this->service->searchByTag('PHP');

        $this->assertCount(1, $results);
        $this->assertEquals('Laravel Book', $results->first()->title);
    }

    /** @test */
    public function it_gets_recent_entities()
    {
        $recent = $this->service->recent(7); // آخر 7 أيام

        $this->assertCount(4, $recent); // جميعها حديثة
    }

    /** @test */
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
}
