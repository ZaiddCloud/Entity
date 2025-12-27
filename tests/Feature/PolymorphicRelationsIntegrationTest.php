<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use App\Models\Tag;
use App\Models\Activity;
use App\Services\EntityManagerService;
use App\Services\EntityQueryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PolymorphicRelationsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function full_polymorphic_system_works()
    {

        // 1. إنشاء جميع أنواع الـ Entities عبر الفاكتوريز
        $book = Book::factory()->create(['title' => 'Integration Book']);
        $video = Video::factory()->create(['title' => 'Integration Video']);
        $audio = Audio::factory()->create(['title' => 'Integration Audio']);
        $manuscript = Manuscript::factory()->create(['title' => 'Integration Manuscript']);

        // 2. إنشاء tags مشتركة عبر الفاكتوريز
        $phpTag = Tag::factory()->create(['name' => 'PHP']);
        $tutorialTag = Tag::factory()->create(['name' => 'Tutorial']);
        $historyTag = Tag::factory()->create(['name' => 'History']);

        // 3. إرفاق tags لجميع الـ Entities
        $book->tags()->attach([$phpTag->id, $tutorialTag->id]);
        $video->tags()->attach($tutorialTag->id);
        $audio->tags()->attach($phpTag->id);
        $manuscript->tags()->attach($historyTag->id);

        // 4. التحقق من العلاقات
        $this->assertCount(2, $book->tags);
        $this->assertCount(1, $video->tags);
        $this->assertCount(1, $audio->tags);
        $this->assertCount(1, $manuscript->tags);

        $bookActivity = Activity::factory()->create([
            'entity_id' => $book->id,
            'entity_type' => 'book',
            'activity_type' => 'created'
        ]);

        $book->refresh();
        $this->assertCount(1, $book->activities);
        $this->assertEquals('created', $book->activities->first()->activity_type);

        // 6. اختبار Services
        $entityService = new EntityManagerService();
        $queryService = new EntityQueryService();

        // البحث عبر جميع الـ Entities
        $results = $queryService->search('Integration');
        $this->assertCount(4, $results);

        // البحث حسب tag
        $phpItems = $queryService->searchByTag('PHP');
        $this->assertCount(2, $phpItems);

        // 7. اختبار الحذف والاستعادة
        $entityService->delete($book);
        $this->assertSoftDeleted($book);

        $entityService->restore($book);
        $this->assertFalse($book->fresh()->trashed());

        $this->assertTrue(true); // كل شيء يعمل!
    }

    /** @test */
    public function entity_manager_service_handles_all_types()
    {
        $service = new EntityManagerService();

        $data = [
            'type' => 'audio',
            'title' => 'Service Created Audio',
            'duration' => 120,
            'format' => 'mp3'
        ];

        $audio = $service->create($data);
        $this->assertInstanceOf(Audio::class, $audio);
        $this->assertEquals('Service Created Audio', $audio->title);
    }
}
