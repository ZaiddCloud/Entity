<?php

namespace Tests\Unit\Traits;


use Tests\TestCase;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Note;
use App\Models\Deletion;
use App\Models\Collection;
use App\Models\Series;
use App\Models\Tag;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CompletePolymorphicSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = \App\Models\User::factory()->create();
    }

    /** @test */
    public function entity_has_all_polymorphic_relationships()
    {
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);

        // التحقق من وجود جميع العلاقات
        $this->assertTrue(method_exists($book, 'activities'));
        $this->assertTrue(method_exists($book, 'comments'));
        $this->assertTrue(method_exists($book, 'notes'));
        $this->assertTrue(method_exists($book, 'tags'));
        $this->assertTrue(method_exists($book, 'categories'));
        $this->assertTrue(method_exists($book, 'collections'));
        $this->assertTrue(method_exists($book, 'series'));
    }

    /** @test */
    public function one_to_many_polymorphic_relationships_work()
    {
        $video = Video::create(['title' => 'Test Video', 'duration' => 120]);

        // إنشاء Activity إضافي يدوياً
        $manualActivity = Activity::create([
            'entity_id' => $video->id,
            'entity_type' => 'video',
            'activity_type' => 'viewed',
            'user_id' => $this->user->id,
            'description' => 'Video was viewed',
        ]);

        $video->refresh();

        // سجلين: 1 من الـ Observer (created) و 1 يدوياً (viewed)
        $this->assertCount(2, $video->activities);
        $this->assertTrue($video->activities->contains('activity_type', 'created'));
        $this->assertTrue($video->activities->contains('activity_type', 'viewed'));

        // إنشاء Comment
        $comment = Comment::create([
            'entity_id' => $video->id,
            'entity_type' => 'video',
            'content' => 'Great video!',
            'user_id' => $this->user->id,
        ]);

        $this->assertCount(1, $video->comments);
        $this->assertEquals('Great video!', $video->comments->first()->content);

        // إنشاء Note
        $note = Note::create([
            'entity_id' => $video->id,
            'entity_type' => 'video',
            'content' => 'Private note',
            'user_id' => $this->user->id,
        ]);

        $this->assertCount(1, $video->notes);
    }

    /** @test */
    public function many_to_many_polymorphic_relationships_work()
    {
        $audio = Audio::create(['title' => 'Test Audio', 'duration' => 180]);
        $tag = Tag::create(['name' => 'Music']);
        $category = Category::create(['name' => 'Entertainment']);

        // إرفاق Tag
        $audio->tags()->attach($tag);
        $audio->refresh();
        $this->assertCount(1, $audio->tags);
        $this->assertEquals('Music', $audio->tags->first()->name);

        // إرفاق Category
        $audio->categories()->attach($category);
        $audio->refresh();
        $this->assertCount(1, $audio->categories);
        $this->assertEquals('Entertainment', $audio->categories->first()->name);
    }

    /** @test */
    public function collection_and_series_relationships_work()
    {
        $book = Book::create(['title' => 'Book 1', 'author' => 'Author 1']);
        $video = Video::create(['title' => 'Video 1', 'duration' => 100]);

        // Collection
        $collection = Collection::create([
            'name' => 'My Favorites',
            'user_id' => $this->user->id,
            'description' => 'My favorite content',
            'is_public' => true,
        ]);

        $book->collections()->attach($collection, ['order_column' => 1, 'added_at' => now()]);
        $video->collections()->attach($collection, ['order_column' => 2, 'added_at' => now()]);

        $collection->refresh();

        $this->assertCount(2, $collection->entities);
        $this->assertInstanceOf(Book::class, $collection->entities[0]);
        $this->assertInstanceOf(Video::class, $collection->entities[1]);

        // Series
        $series = Series::create([
            'title' => 'Learning Series',
            'description' => 'Learn step by step',
            'order_column' => 1,
        ]);

        $book->series()->attach($series, ['position' => 1]);
        $video->series()->attach($series, ['position' => 2]);

        $series->refresh();

        $this->assertCount(2, $series->entities);
        $this->assertEquals('Book 1', $series->entities[0]->title);
        $this->assertEquals('Video 1', $series->entities[1]->title);
    }

    /** @test */
    public function deletion_records_work_with_soft_deletes()
    {
        $manuscript = Manuscript::create([
            'title' => 'Old Manuscript',
            'author' => 'Ancient Author',
            'century' => 10,
        ]);

        // تسجيل الحذف
        $deletion = Deletion::create([
            'entity_id' => $manuscript->id,
            'entity_type' => 'manuscript',
            'user_id' => $this->user->id,
            'reason' => 'Test deletion',
        ]);

        $this->assertEquals('Test deletion', $deletion->reason);
        $this->assertInstanceOf(Manuscript::class, $deletion->entity);

        // Soft delete
        $manuscript->delete();
        $this->assertSoftDeleted($manuscript);
    }

    /** @test */
    public function all_entity_types_support_same_relationships()
    {
        $entities = [
            Book::create(['title' => 'Book', 'author' => 'Author']),
            Video::create(['title' => 'Video', 'duration' => 120]),
            Audio::create(['title' => 'Audio', 'duration' => 180]),
            Manuscript::create(['title' => 'Manuscript', 'author' => 'Old', 'century' => 12]),
        ];

        $tag = Tag::create(['name' => 'Test Tag']);

        foreach ($entities as $entity) {
            // Tags تعمل مع جميع الأنواع
            $entity->tags()->attach($tag);
            $entity->refresh();
            $this->assertCount(1, $entity->tags);

            // Activities تعمل مع جميع الأنواع
            // الـ Observer قد أنشأ فعلاً سجل 'created'
            Activity::create([
                'entity_id' => $entity->id,
                'entity_type' => $this->getMorphType($entity),
                'activity_type' => 'viewed',
                'user_id' => $this->user->id,
                'description' => 'Was viewed',
            ]);

            $entity->refresh();

            // سجلين: created (observer) + viewed (manual)
            $this->assertCount(2, $entity->activities);
        }
    }

    /** @test */
    public function morph_map_works_correctly_for_all_types()
    {
        // التحقق من أن morphMap يعمل
        $book = Book::create(['title' => 'Morph Test', 'author' => 'Author']);
        $activity = Activity::create([
            'entity_id' => $book->id,
            'entity_type' => 'book', // ليس App\Models\Book
            'activity_type' => 'test',
            'user_id' => $this->user->id,
            'description' => 'Test morph',
        ]);

        // يجب أن يعرف Laravel أن 'book' = Book::class
        $this->assertInstanceOf(Book::class, $activity->entity);
        $this->assertEquals($book->id, $activity->entity->id);
    }

    /** @test */
    public function complex_scenarios_work()
    {
        // سيناريو معقد: كتاب مع tags، activities، وفي collection

        $book = Book::create(['title' => 'Complex Book', 'author' => 'Complex Author']);

        // إضافة tags
        $tag1 = Tag::create(['name' => 'Fiction']);
        $tag2 = Tag::create(['name' => 'Adventure']);
        $book->tags()->attach([$tag1->id, $tag2->id]);

        // إضافة activities
        Activity::create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'activity_type' => 'created',
            'user_id' => $this->user->id,
            'description' => 'Book created',
        ]);

        Activity::create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'activity_type' => 'updated',
            'user_id' => $this->user->id,
            'description' => 'Book updated',
        ]);

        // إضافة إلى collection
        $collection = Collection::create([
            'name' => 'Complex Collection',
            'user_id' => $this->user->id,
            'is_public' => true,
        ]);

        $book->collections()->attach($collection, [
            'order_column' => 1,
            'added_at' => now(),
        ]);

        // التحقق
        $book->refresh();
        $collection->refresh();

        $this->assertCount(2, $book->tags);
        // 3 سجلات: created (observer) + created (manual) + updated (manual)
        $this->assertCount(3, $book->activities);
        $this->assertCount(1, $book->collections);
        $this->assertCount(1, $collection->entities);
    }

    /**
     * Helper method
     */
    private function getMorphType($entity): string
    {
        $map = [
            Book::class => 'book',
            Video::class => 'video',
            Audio::class => 'audio',
            Manuscript::class => 'manuscript',
        ];

        return $map[get_class($entity)];
    }
}
