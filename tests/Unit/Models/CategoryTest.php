<?php
// tests/Unit/Models/CategoryTest.php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Category;
use App\Models\Book;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function category_can_be_created_with_basic_fields()
    {
        // 1. Arrange
        $data = [
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
            'description' => 'Books about science fiction',
        ];

        // 2. Act
        $category = Category::create($data);

        // 3. Assert
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Science Fiction', $category->name);
        $this->assertEquals('science-fiction', $category->slug);
        $this->assertEquals('Books about science fiction', $category->description);
        $this->assertDatabaseHas('categories', $data);
    }

    /** @test */
    // tests/Unit/Models/CategoryTest.php

    /** @test */
    public function category_slug_is_generated_from_name_if_not_provided()
    {
        // 1. Arrange
        $data = [
            'name' => 'Science Fiction Books',
            'description' => 'Category for sci-fi books',
        ];

        // 2. Act
        $category = Category::create($data);

        // 3. Assert
        $this->assertEquals('science-fiction-books', $category->slug);
        $this->assertDatabaseHas('categories', [
            'name' => 'Science Fiction Books',
            'slug' => 'science-fiction-books',
        ]);
    }

    /** @test */
    public function category_slug_is_unique_and_incremented_on_conflict()
    {
        // 1. Arrange - إنشاء أول كاتيجوري
        Category::create([
            'name' => 'Fantasy',
            'slug' => 'fantasy',
        ]);

        // 2. Act - محاولة إنشاء كاتيجوري بنفس الاسم
        $category = Category::create([
            'name' => 'Fantasy',
        ]);

        // 3. Assert
        $this->assertEquals('fantasy-1', $category->slug);
        $this->assertDatabaseHas('categories', [
            'name' => 'Fantasy',
            'slug' => 'fantasy-1',
        ]);
    }

    /** @test */
    // tests/Unit/Models/CategoryTest.php

    /** @test */
    public function category_can_have_parent_category()
    {
        // 1. Arrange
        $parent = Category::create([
            'name' => 'Fiction',
            'slug' => 'fiction',
        ]);

        // 2. Act
        $child = Category::create([
            'name' => 'Science Fiction',
            'slug' => 'science-fiction',
            'parent_id' => $parent->id,
        ]);

        // 3. Assert
        $this->assertEquals($parent->id, $child->parent_id);
        $this->assertTrue($child->parent->is($parent));
    }

    /** @test */
    public function category_can_have_children_categories()
    {
        // 1. Arrange
        $parent = Category::create([
            'name' => 'Non-Fiction',
            'slug' => 'non-fiction',
        ]);

        $child1 = Category::create([
            'name' => 'Biography',
            'slug' => 'biography',
            'parent_id' => $parent->id,
        ]);

        $child2 = Category::create([
            'name' => 'History',
            'slug' => 'history',
            'parent_id' => $parent->id,
        ]);

        // 2. Act
        $children = $parent->children;

        // 3. Assert
        $this->assertCount(2, $children);
        $this->assertTrue($children->contains($child1));
        $this->assertTrue($children->contains($child2));
    }

    /** @test */
    public function category_returns_root_categories_when_parent_id_is_null()
    {
        // 1. Arrange
        $root1 = Category::create(['name' => 'Fiction', 'slug' => 'fiction']);
        $root2 = Category::create(['name' => 'Non-Fiction', 'slug' => 'non-fiction']);

        // 2. Act
        $roots = Category::whereNull('parent_id')->get();

        // 3. Assert
        $this->assertCount(2, $roots);
        $this->assertTrue($roots->contains($root1));
        $this->assertTrue($roots->contains($root2));
    }

    // tests/Unit/Models/CategoryTest.php

    /** @test */
    public function category_can_have_polymorphic_relationship_with_entities()
    {
        // 1. Arrange
        $category = Category::create(['name' => 'Tech', 'slug' => 'tech']);
        $book = Book::factory()->create(['title' => 'Tech Book']);
        $video = Video::factory()->create(['title' => 'Tech Video']);

        // 2. Act
        $book->categories()->attach($category);
        $video->categories()->attach($category);

        // 3. Assert
        $this->assertCount(1, $category->fresh()->books);
        $this->assertCount(1, $category->fresh()->videos);
    }
    // tests/Unit/Models/CategoryTest.php

    /** @test */
    public function category_name_is_required()
    {
        // 1. Arrange
        $this->expectException(\Illuminate\Database\QueryException::class);

        // 2. Act
        Category::create([
            'slug' => 'test-slug',
            'description' => 'Test description',
        ]);
    }

    /** @test */
    public function category_slug_is_unique()
    {
        // 1. Arrange
        Category::create([
            'name' => 'First Category',
            'slug' => 'unique-slug',
        ]);

        // 2. Act & Assert
        $this->expectException(\Illuminate\Database\QueryException::class);

        Category::create([
            'name' => 'Second Category',
            'slug' => 'unique-slug', // نفس الـ slug
        ]);
    }

    /** @test */
    public function category_can_be_soft_deleted_if_configured()
    {
        // 1. Arrange
        $category = Category::create(['name' => 'Deletable', 'slug' => 'deletable']);

        // 2. Act
        $category->delete();

        // 3. Assert
        $this->assertSoftDeleted('categories', ['id' => $category->id]);
        $this->assertNotNull(Category::withTrashed()->find($category->id)->deleted_at);
        $this->assertNull(Category::find($category->id));
    }

    /** @test */
    // tests/Unit/Models/CategoryTest.php
    /** @test */
    public function category_can_get_parent_hierarchy()
    {
        // 1. Arrange
        $grandParent = Category::create(['name' => 'Root', 'slug' => 'root']);
        $parent = Category::create(['name' => 'Parent', 'slug' => 'parent', 'parent_id' => $grandParent->id]);
        $child = Category::create(['name' => 'Child', 'slug' => 'child', 'parent_id' => $parent->id]);

        // 2. Act & Assert
        $this->assertEquals($parent->id, $child->parent->id);
        $this->assertEquals($grandParent->id, $parent->parent->id);
        $this->assertEquals($grandParent->id, $child->parent->parent->id);

        // اختبار الوصول للجذور
        $this->assertNull($grandParent->parent_id);
        $this->assertCount(1, $grandParent->children);
    }
}
