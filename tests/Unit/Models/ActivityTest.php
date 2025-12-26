<?php

namespace Tests\Unit\Models;

use App\Models\User;
use Tests\TestCase;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Note;
use App\Models\Deletion;
use App\Models\Collection;
use App\Models\Series;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActivityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // إنشاء مستخدم لجميع الاختبارات
        $this->user = User::factory()->create();
    }

    public function testActivityCanRecordEventsForAllEntityTypes()
    {
        // تحقق من وجود مستخدمين
        if (User::count() === 0) {
            $user = User::factory()->create();
        } else {
            $user = User::first();
        }

        $book = \App\Models\Book::factory()->create();

        $activity = Activity::create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'activity_type' => 'created',
            'user_id' => $user->id,
            'description' => 'Book was created'
        ]);

        $this->assertInstanceOf(Activity::class, $activity);
    }
    /** @test */
    public function activity_can_record_events_for_all_entity_types()
    {
        $user = User::factory()->create();
        $entities = [
            Book::create(['title' => 'Test Book', 'author' => 'Author']),
            Video::create(['title' => 'Test Video', 'duration' => 120]),
            Audio::create(['title' => 'Test Audio', 'duration' => 180]),
            Manuscript::create(['title' => 'Test Manuscript', 'author' => 'Old Author', 'century' => 14]),
        ];

        foreach ($entities as $entity) {
            $activity = Activity::create([
                'entity_id' => $entity->id,
                'entity_type' => $this->getMorphType($entity),
                'activity_type' => 'created',
                'user_id' => $user->id,
                'description' => class_basename($entity) . ' was created',
            ]);

            $this->assertNotNull($activity);
            $this->assertEquals($entity->id, $activity->entity->id);
        }
    }

    /** @test */
    public function comment_can_be_added_to_all_entity_types()
    {
        $book = Book::create(['title' => 'Comment Test Book', 'author' => 'Author']);

        $comment = Comment::create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'content' => 'This is a test comment on a book',
            'user_id' => 1,
        ]);

        $this->assertEquals('This is a test comment on a book', $comment->content);
        $this->assertInstanceOf(Book::class, $comment->entity);
        $this->assertEquals($book->id, $comment->entity->id);
    }

    /** @test */
    public function note_can_be_attached_to_entities()
    {
        $video = Video::create(['title' => 'Note Test Video', 'duration' => 150]);

        $note = Note::create([
            'entity_id' => $video->id,
            'entity_type' => 'video',
            'content' => 'Private note about this video',
            'user_id' => 1,
        ]);

        $this->assertEquals('Private note about this video', $note->content);
        $this->assertInstanceOf(Video::class, $note->entity);
    }

    /** @test */
    public function deletion_records_entity_deletions()
    {
        $audio = Audio::create(['title' => 'To Delete Audio', 'duration' => 200]);

        $deletion = Deletion::create([
            'entity_id' => $audio->id,
            'entity_type' => 'audio',
            'user_id' => 1,
            'reason' => 'Test deletion recording',
        ]);

        $this->assertEquals('Test deletion recording', $deletion->reason);
        $this->assertInstanceOf(Audio::class, $deletion->entity);

        // Soft delete the audio
        $audio->delete();
        $this->assertSoftDeleted($audio);
    }

    /** @test */
    public function collection_can_contain_multiple_entity_types()
    {
        $collection = Collection::create([
            'name' => 'My Test Collection',
            'user_id' => 1,
            'description' => 'Collection for testing',
            'is_public' => true,
        ]);

        $book = Book::create(['title' => 'Collection Book', 'author' => 'Author']);
        $video = Video::create(['title' => 'Collection Video', 'duration' => 100]);

        // Add entities to collection
        $collection->addEntity($book);
        $collection->addEntity($video);

        $this->assertCount(2, $collection->entities);
        $this->assertInstanceOf(Book::class, $collection->entities[0]);
        $this->assertInstanceOf(Video::class, $collection->entities[1]);
    }

    /** @test */
    public function series_can_order_entities()
    {
        $series = Series::create([
            'title' => 'Test Series',
            'description' => 'A series for testing',
            'order_column' => 1,
        ]);

        $manuscript1 = Manuscript::create(['title' => 'Series MS 1', 'author' => 'Author', 'century' => 12]);
        $manuscript2 = Manuscript::create(['title' => 'Series MS 2', 'author' => 'Author', 'century' => 13]);

        // Add entities to series with positions
        $series->addEntity($manuscript1, 1);
        $series->addEntity($manuscript2, 2);

        $entities = $series->entities;

        $this->assertCount(2, $entities);
        $this->assertEquals('Series MS 1', $entities[0]->title);
        $this->assertEquals('Series MS 2', $entities[1]->title);
    }

    /** @test */
    public function all_polymorphic_models_share_common_structure()
    {
        $models = [
            Activity::class,
            Comment::class,
            Note::class,
            Deletion::class,
        ];

        foreach ($models as $modelClass) {
            $model = new $modelClass();

            // All should have entity_id and entity_type
            $this->assertContains('entity_id', $model->getFillable());
            $this->assertContains('entity_type', $model->getFillable());

            // All should have morphTo relationship
            $this->assertTrue(method_exists($model, 'entity'));
        }
    }

    /** @test */
    public function many_to_many_models_have_entities_relationship()
    {
        $models = [
            Collection::class,
            Series::class,
        ];

        foreach ($models as $modelClass) {
            $model = new $modelClass();
            $this->assertTrue(method_exists($model, 'getEntitiesAttribute'));
        }
    }

    /**
     * Helper to get morph type for entity
     */
    private function getMorphType($entity): string
    {
        $map = [
            Book::class => 'book',
            Video::class => 'video',
            Audio::class => 'audio',
            Manuscript::class => 'manuscript',
        ];

        return $map[get_class($entity)] ?? strtolower(class_basename($entity));
    }
}
