<?php

namespace Tests\Unit\Traits;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Traits\HasPolymorphicRelations;
use App\Models\Tag;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Schema;

// نموذج تجريبي يستخدم جداول منفصلة
class TestEntity extends Model
{
    use HasPolymorphicRelations, HasUuids;

    protected $table = 'test_entities';
    protected $fillable = ['name'];

    protected $tagsPivotTable = 'test_taggables';
    protected $categoriesPivotTable = 'test_categorizables';

    // تجاوز علاقة tags لاستخدام pivot table مختلف
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'entity', 'test_taggables')
            ->withTimestamps();
    }
}

class HasPolymorphicRelationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // إنشاء جدول test_entities إذا لم يكن موجوداً
        if (!Schema::hasTable('test_entities')) {
            Schema::create('test_entities', function ($table) {
                $table->uuid('id')->primary();
                $table->string('name');
                $table->timestamps();
            });
        }

        // إنشاء جدول test_taggables منفصل (وليس taggables العام)
        if (!Schema::hasTable('test_taggables')) {
            Schema::create('test_taggables', function ($table) {
                // $table->id();
                $table->foreignUuid('tag_id');
                $table->uuidMorphs('entity'); // NOT nullable
                $table->timestamps();

                $table->index(['entity_id', 'entity_type']);
                // $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade'); // foreignUuid handles constraint? No, need to be explicit or use constrained method if creating table
                // Since 'tag_id' is foreignUuid, if we want constraint:
                $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');

                // منع التكرار
                $table->unique(['tag_id', 'entity_id', 'entity_type'], 'test_taggable_unique');
            });
        }
    }

    /*protected function tearDown(): void
    {
        // إسقاط الجداول المؤقتة فقط
        Schema::dropIfExists('test_taggables');
        Schema::dropIfExists('test_entities');
        parent::tearDown();
    }*/

    /** @test */
    public function trait_adds_polymorphic_methods()
    {
        $model = new TestEntity();

        // التحقق من وجود الطرق
        $this->assertTrue(method_exists($model, 'activities'));
        $this->assertTrue(method_exists($model, 'tags'));
    }

    /** @test */
    public function tags_relationship_works()
    {
        // إنشاء tag أولاً
        $tag = Tag::create(['name' => 'PHP Testing']);

        // إنشاء entity تجريبية
        $entity = TestEntity::create(['name' => 'Test Entity']);

        // إرفاق tag
        $entity->tags()->attach($tag->id);

        // تحميل العلاقة من جديد
        $entity->load('tags');

        // التحقق
        $this->assertCount(1, $entity->tags);
        $this->assertEquals('PHP Testing', $entity->tags->first()->name);
        $this->assertEquals('php-testing', $entity->tags->first()->slug);
    }

    /** @test */
    public function morph_map_works_for_tags()
    {
        // Arrange
        $book = Book::create(['title' => 'Test Book', 'author' => 'Author']);
        $tag = Tag::create(['name' => 'Test Tag']);

        // Act
        $book->tags()->attach($tag);

        // Assert - يجب أن تعمل العلاقة العكسية الآن
        $this->assertCount(1, $tag->entities);
        $this->assertInstanceOf(Book::class, $tag->entities->first());
    }
}
