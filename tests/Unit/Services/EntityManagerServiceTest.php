<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\EntityManagerService;
use App\Models\Book;
use App\Models\Video;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class EntityManagerServiceTest extends TestCase
{
    use RefreshDatabase;

    private EntityManagerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EntityManagerService();
    }

    #[Test]
    public function service_can_be_instantiated()
    {
        $this->assertInstanceOf(EntityManagerService::class, $this->service);
    }

    #[Test]
    public function it_creates_book_entity_with_valid_data()
    {
        // RED: Service غير موجود أو ليس لديه create method
        $data = [
            'type' => 'book',
            'title' => 'New Book Title',
            'author' => 'Book Author',
            'isbn' => '1234567890123'
        ];

        $entity = $this->service->create($data);

        $this->assertInstanceOf(Book::class, $entity);
        $this->assertEquals('New Book Title', $entity->title);
        $this->assertEquals('new-book-title', $entity->slug);
        $this->assertEquals('Book Author', $entity->author);
    }

    #[Test]
    public function it_creates_video_entity()
    {
        $data = [
            'type' => 'video',
            'title' => 'Tutorial Video',
            'duration' => 3600,
            'format' => 'mp4'
        ];

        $entity = $this->service->create($data);

        $this->assertInstanceOf(Video::class, $entity);
        $this->assertEquals('Tutorial Video', $entity->title);
        $this->assertEquals(3600, $entity->duration);
    }

    #[Test]
    public function it_throws_invalid_argument_for_unhandled_type()
    {
        $this->expectException(\InvalidArgumentException::class);

        // استخدام reflection لتجاوز الـ validation
        $reflection = new \ReflectionClass($this->service);
        $method = $reflection->getMethod('resolveEntityClass');
        $method->setAccessible(true);

        // استدعاء مباشر بدون validation
        $method->invoke($this->service, 'unknown_type');
    }

    // في tests/Unit/Services/EntityManagerServiceTest.php
    #[Test]
    public function it_throws_validation_exception_for_invalid_entity_type()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'type' => 'invalid_type',
            'title' => 'Test'
        ];

        $this->service->create($data);
    }

    #[Test]
    public function it_updates_existing_entity()
    {
        $book = Book::create([
            'title' => 'Original Title',
            'author' => 'Author'
        ]);

        $updated = $this->service->update($book, [
            'title' => 'Updated Title'
        ]);

        $this->assertTrue($updated);
        $this->assertEquals('Updated Title', $book->fresh()->title);
        $this->assertEquals('updated-title', $book->fresh()->slug);
    }

    #[Test]
    public function it_deletes_entity_softly()
    {
        $book = Book::create([
            'title' => 'Book to Delete',
            'author' => 'Author'
        ]);

        $deleted = $this->service->delete($book);

        $this->assertTrue($deleted);
        $this->assertSoftDeleted($book);
    }

    #[Test]
    public function it_restores_soft_deleted_entity()
    {
        $book = Book::create([
            'title' => 'Book to Restore',
            'author' => 'Author'
        ]);

        $book->delete();

        $restored = $this->service->restore($book);

        $this->assertTrue($restored);
        $this->assertFalse($book->fresh()->trashed());
    }

    #[Test]
    public function it_updates_slug_when_title_changes()
    {
        $book = Book::create([
            'title' => 'Original Title',
            'author' => 'Author'
        ]);

        $this->assertEquals('original-title', $book->slug);

        $this->service->update($book, [
            'title' => 'Completely New Title'
        ]);

        $this->assertEquals('completely-new-title', $book->fresh()->slug);
    }

    // tests/Unit/Services/EntityManagerServiceTest.php - عدّل آخر test
    #[Test]
    public function it_provides_validation_messages()
    {
        try {
            $this->service->create([
                'type' => 'invalid_type',
                'title' => 'Test'
            ]);

            $this->fail('Expected ValidationException was not thrown');
        } catch (ValidationException $e) {
            $errors = $e->errors();

            // فقط تحقق من وجود خطأ في حقل type
            $this->assertArrayHasKey('type', $errors);
            $this->assertNotEmpty($errors['type']);
        }
    }
}
