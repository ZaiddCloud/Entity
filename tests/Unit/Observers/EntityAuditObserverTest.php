<?php
// tests/Unit/Observers/EntityAuditObserverTest.php

namespace Tests\Unit\Observers;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;

class EntityAuditObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Log facade لجميع الاختبارات
        Log::spy();
    }

    /**
     * @test
     * @todo هذه المهمة مكررة مع EntityLifecycleObserver
     * سيتم التعامل مع ازالة التكرار او اعادة هيكلة الـ Observers لاحقا
     * حالياً يوجد تعارض بين الـ Observers في استخدام Log::info()
     */
    public function it_logs_when_book_is_created()
    {
        // Act
        $book = Book::factory()->create(['title' => 'Test Book']);

        // Assert
        $this->assertDatabaseHas('activities', [
            'entity_type' => 'book',
            'entity_id' => $book->id,
            'activity_type' => 'created'
        ]);
    }

    /**
     * @test
     * @todo هذه المهمة مكررة مع EntityLifecycleObserver
     * حالياً EntityLifecycleObserver يقوم بتسجيل created event
     * مما يتسبب في تعارض مع EntityAuditObserver
     */
    public function it_logs_when_video_is_created()
    {
        // Act
        $video = Video::factory()->create(['title' => 'Test Video']);

        // Assert
        $this->assertDatabaseHas('activities', [
            'entity_type' => 'video',
            'entity_id' => $video->id,
            'activity_type' => 'created'
        ]);
    }

    /**
     * @test
     * @todo هذه المهمة مكررة مع EntityLifecycleObserver
     * يجب تحديد مسؤوليات كل Observer بشكل واضح
     */
    public function it_logs_when_audio_is_created()
    {
        // Act
        $audio = Audio::factory()->create(['title' => 'Test Audio']);

        // Assert
        $this->assertDatabaseHas('activities', [
            'entity_type' => 'audio',
            'entity_id' => $audio->id,
            'activity_type' => 'created'
        ]);
    }

    /**
     * @test
     * @todo هذه المهمة مكررة مع EntityLifecycleObserver
     * EntityAuditObserver يجب أن يركز على التدقيق فقط وليس السلوك الأساسي
     */
    public function it_logs_when_manuscript_is_created()
    {
        // Act
        $manuscript = Manuscript::factory()->create(['title' => 'Test Manuscript']);

        // Assert
        $this->assertDatabaseHas('activities', [
            'entity_type' => 'manuscript',
            'entity_id' => $manuscript->id,
            'activity_type' => 'created'
        ]);
    }

    /**
     * @test
     * @todo هذه المهمة مكررة مع EntityLifecycleObserver
     * حالياً EntityLifecycleObserver يقوم بتسجيل updated event
     */
    public function it_logs_updates_with_changes()
    {
        // Arrange
        $book = Book::factory()->create(['title' => 'Original']);

        // Act
        $book->update(['title' => 'Updated']);

        // Assert
        $this->assertDatabaseHas('activities', [
            'entity_type' => 'book',
            'entity_id' => $book->id,
            'activity_type' => 'updated'
        ]);

        $activity = \App\Models\Activity::where('entity_id', $book->id)
            ->where('activity_type', 'updated')
            ->first();
        
        $this->assertNotNull($activity->changes);
        $this->assertEquals('Updated', $activity->changes['title']);
    }

    /**
     * @test
     * @todo هذه المهمة مكررة مع EntityLifecycleObserver
     * EntityLifecycleObserver يقوم بتسجيل deleted event
     * EntityAuditObserver يجب أن يركز على audit trail كامل
     */
    public function it_logs_deletions()
    {
        // Arrange
        $video = Video::factory()->create();

        // Act
        $video->delete();

        // Assert
        $this->assertDatabaseHas('activities', [
            'entity_type' => 'video',
            'entity_id' => $video->id,
            'activity_type' => 'deleted'
        ]);
    }

    /**
     * @test
     * @todo اختبار جديد سيتم اضافته لاحقاً
     * EntityAuditObserver يجب أن يركز على:
     * 1. إنشاء audit trail كامل
     * 2. تسجيل تغييرات الحقول بدقة
     * 3. حفظ metadata إضافية (مستخدم، IP، وقت)
     */
    public function it_creates_complete_audit_trail()
    {
        // Arrange
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user);

        // Act
        $book = Book::factory()->create();
        $book->update(['title' => 'New Title']);
        $book->delete();

        // Assert
        $this->assertCount(3, \App\Models\Activity::where('entity_id', $book->id)->get());
        $this->assertDatabaseHas('activities', [
            'entity_id' => $book->id,
            'user_id' => $user->id,
            'activity_type' => 'created'
        ]);
    }

    /**
     * @test
     * @todo اختبار جديد سيتم اضافته لاحقاً
     * للتحقق من أن EntityAuditObserver يسجل في قاعدة بيانات audit_logs
     */
    public function it_saves_audit_logs_to_database()
    {
        // This is covered by other tests but specifically checking the record count
        $count = \App\Models\Activity::count();
        Book::factory()->create();
        $this->assertEquals($count + 1, \App\Models\Activity::count());
    }

}
