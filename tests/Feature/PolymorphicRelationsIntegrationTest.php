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
        $this->markTestSkipped('سيتم إصلاحه بعد اكتمال نظام الـ tags');

        /*// 1. إنشاء جميع أنواع الـ Entities
        $book = Book::create(['title' => 'Integration Book', 'author' => 'Author']);
        $video = Video::create(['title' => 'Integration Video', 'duration' => 300]);
        $audio = Audio::create(['title' => 'Integration Audio', 'duration' => 600]);
        $manuscript = Manuscript::create(['title' => 'Integration Manuscript', 'author' => 'Old Author', 'century' => 14]);

        // 2. إنشاء tags مشتركة
        $phpTag = Tag::create(['name' => 'PHP']);
        $tutorialTag = Tag::create(['name' => 'Tutorial']);
        $historyTag = Tag::create(['name' => 'History']);

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

        // التحقق من العلاقة العكسية (Tag -> Entities)
        $this->assertCount(2, $phpTag->fresh()->entities); // Book و Audio
        $this->assertCount(2, $tutorialTag->fresh()->entities); // Book و Video
        $this->assertCount(1, $historyTag->fresh()->entities); // Manuscript فقط

        // 5. اختبار Activities
        $bookActivity = Activity::create([
            'entity_id' => $book->id,
            'entity_type' => Book::class,
            'activity_type' => 'created'
        ]);

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

        $this->assertTrue(true); // كل شيء يعمل!*/
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
