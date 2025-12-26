<?php
// tests/Unit/Models/BookTest.php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;
use App\Models\Book;
use App\Models\Tag;
use App\Models\Activity;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function book_extends_entity_abstract_class()
    {
        $book = new Book();
        $this->assertInstanceOf(\App\Models\Entity::class, $book);
    }

    /** @test */
    public function book_has_required_properties()
    {
        $book = Book::create([
            'title' => 'Test Book',
            'author' => 'Test Author',
        ]);

        $this->assertEquals('Test Book', $book->title);
        $this->assertEquals('Test Author', $book->author);
        $this->assertNotNull($book->slug);
    }

    /** @test */
    public function book_inherits_polymorphic_relations_from_entity()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        // التحقق من وجود طرق العلاقات
        $this->assertTrue(method_exists($book, 'tags'), 'Book should have tags() method');
        $this->assertTrue(method_exists($book, 'activities'), 'Book should have activities() method');
        $this->assertTrue(method_exists($book, 'categories'), 'Book should have categories() method');

        // اختبار علاقة tags مع إصلاح
        $tag = Tag::create(['name' => 'Programming']);

        // 1. تأكد من أن التاج تم إنشاؤه
        $this->assertNotNull($tag->id, 'Tag should have an ID');

        // 2. أرفق التاج
        $book->tags()->attach($tag->id);

        // 3. أعد تحميل العلاقة
        $book->refresh()->load('tags');

        // 4. تحقق من العلاقة
        $this->assertCount(1, $book->tags, 'Book should have 1 tag after attaching');

        if ($book->tags->isNotEmpty()) {
            $this->assertEquals('Programming', $book->tags->first()->name);
        }

        // 5. تحقق من pivot table
        $this->assertDatabaseHas('taggables', [
            'tag_id' => $tag->id,
            'entity_id' => $book->id,
            'entity_type' => 'book',
        ]);
    }

    /** @test */
    public function book_can_have_activities()
    {
        $user = User::factory()->create();
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        // التحقق من وجود طريقة activities
        $this->assertTrue(method_exists($book, 'activities'));

        // إنشاء activity
        Activity::create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'activity_type' => 'created',
            'description' => 'Book created',
            'user_id' => $user->id,
        ]);

        // تحميل العلاقة
        $book->load('activities');

        // التحقق
        $this->assertCount(1, $book->activities, 'Book should have 1 activity');

        if ($book->activities->isNotEmpty()) {
            $this->assertEquals('created', $book->activities->first()->activity_type);
        }

        // تحقق من قاعدة البيانات
        $this->assertDatabaseHas('activities', [
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'activity_type' => 'created',
        ]);
    }

    /** @test */
    public function book_supports_soft_deletes()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        $book->delete();

        $this->assertSoftDeleted($book);
        $this->assertNotNull($book->deleted_at);
    }
}
