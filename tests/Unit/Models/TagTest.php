<?php

namespace Tests\Unit\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Tag;
use App\Models\Book;
use App\Models\Video;

class TagTest extends TestCase
{
    use RefreshDatabase;
    /** @test */
    public function tag_has_name_and_slug()
    {
        // RED: Tag model غير موجود
        $tag = new Tag([
            'name' => 'PHP',
            'slug' => 'php'
        ]);

        $this->assertEquals('PHP', $tag->name);
        $this->assertEquals('php', $tag->slug);
    }

    /** @test */
    public function tag_slug_is_generated_from_name()
    {
        $tag = Tag::create([
            'name' => 'Laravel Framework'
        ]);

        $this->assertEquals('laravel-framework', $tag->slug);
    }

    /** @test */
    public function tag_can_have_type()
    {
        $tag = Tag::create([
            'name' => 'Programming',
            'type' => 'category'
        ]);

        $this->assertEquals('category', $tag->type);
    }

    // tests/Unit/Models/TagTest.php - إضافة اختبار
    /** @test */
    public function tag_has_polymorphic_taggables_relationship()
    {
        $tag = Tag::create(['name' => 'Test Tag']);

        $book = Book::create(['title' => 'Book 1', 'author' => 'Author']);
        $video = Video::create(['title' => 'Video 1', 'duration' => 300]);

        // إرفاق الـ tag
        $book->tags()->attach($tag);
        $video->tags()->attach($tag);

        // استخدم العلاقة الجديدة
        $taggables = $tag->taggables;

        $this->assertCount(2, $taggables);

        // التحقق من أنواع الـ Models
        $types = $taggables->map(function ($model) {
            return get_class($model);
        })->toArray();

        $this->assertContains(Book::class, $types);
        $this->assertContains(Video::class, $types);

        // يمكننا أيضاً التحقق من البيانات
        $this->assertEquals('Book 1', $taggables->firstWhere('title', 'Book 1')->title);
        $this->assertEquals('Video 1', $taggables->firstWhere('title', 'Video 1')->title);
    }
}
