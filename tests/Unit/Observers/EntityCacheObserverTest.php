<?php
// tests/Unit/Observers/EntityCacheObserverTest.php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Tag;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

class EntityCacheObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();

        // تسجيل الـ Observer
        $observer = \App\Observers\EntityCacheObserver::class;
        Book::observe($observer);
        Tag::observe($observer);
        Category::observe($observer);
    }

    /** @test */
    public function it_invalidates_cache_when_entity_is_created()
    {
        // Arrange
        Cache::remember('entities.recent', 3600, fn() => ['test']);
        Cache::remember('entities.all', 3600, fn() => ['test']);
        Cache::remember('entities.Book.all', 3600, fn() => ['test']);

        // Act
        Book::create(['title' => 'Test Book', 'author' => 'Author']);

        // Assert
        $this->assertFalse(Cache::has('entities.recent'));
        $this->assertFalse(Cache::has('entities.all'));
        $this->assertFalse(Cache::has('entities.Book.all'));
    }

    /** @test */
    public function it_invalidates_tag_cache_when_tag_is_created()
    {
        // Arrange
        Cache::remember('Tags.all', 3600, fn() => ['test']);

        // Act
        Tag::create(['name' => 'Science']);

        // Assert
        $this->assertFalse(Cache::has('Tags.all'));
    }

    /** @test */
    public function it_invalidates_category_cache_when_category_is_created()
    {
        // Arrange
        Cache::remember('Categories.all', 3600, fn() => ['test']);

        // Act
        Category::create(['name' => 'Fiction']);

        // Assert
        $this->assertFalse(Cache::has('Categories.all'));
    }

    /** @test */
    public function it_clears_all_entity_cache()
    {
        // Arrange
        Cache::remember('entities.recent', 3600, fn() => ['data']);
        Cache::remember('entities.popular', 3600, fn() => ['data']);
        Cache::remember('entities.Book.all', 3600, fn() => ['data']);

        $observer = new \App\Observers\EntityCacheObserver();

        // Act
        $observer->clearEntityCache();

        // Assert
        $this->assertFalse(Cache::has('entities.recent'));
        $this->assertFalse(Cache::has('entities.popular'));
        $this->assertFalse(Cache::has('entities.Book.all'));
    }

    /** @test */
    // في tests/Unit/Observers/EntityCacheObserverTest.php

    /** @test */
    public function it_generates_entity_stats_cache()
    {
        // Arrange
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        // Act - اختبار أن الكاش يتم تدفئته
        $cacheKey = "entity.Book.{$book->id}.stats";

        // انتظر قليلاً لضمان عمل الـ Observer
        sleep(0.1);

        // Assert - تحقق من وجود الكاش
        $this->assertTrue(Cache::has($cacheKey));

        // تحقق من محتوى الكاش
        $cachedStats = Cache::get($cacheKey);
        $this->assertIsArray($cachedStats);
        $this->assertArrayHasKey('tags_count', $cachedStats);
        $this->assertArrayHasKey('categories_count', $cachedStats);
    }

// أو

    /** @test */
    public function it_warms_entity_cache_when_created()
    {
        // Arrange & Act
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        // Assert - تحقق من وجود الكاشات الأساسية
        $cacheKey = "entity.Book.{$book->id}";
        $this->assertTrue(Cache::has($cacheKey));

        $cacheKeyWithRelations = "entity.Book.{$book->id}.with_relations";
        $this->assertTrue(Cache::has($cacheKeyWithRelations));

        $cacheKeyStats = "entity.Book.{$book->id}.stats";
        $this->assertTrue(Cache::has($cacheKeyStats));
    }
}
