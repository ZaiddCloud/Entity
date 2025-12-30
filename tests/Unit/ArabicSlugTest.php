<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Book;
use App\Models\Tag;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class ArabicSlugTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_generates_arabic_slugs_for_books()
    {
        $book = Book::create([
            'title' => 'كتاب جديد',
            'author' => 'مؤلف غير معروف',
            'isbn' => '1234567890123'
        ]);

        $this->assertEquals('كتاب-جديد', $book->slug);
    }

    #[Test]
    public function it_generates_arabic_slugs_for_tags()
    {
        $tag = Tag::create([
            'name' => 'برمجة لارايفل'
        ]);

        $this->assertEquals('برمجة-لارايفل', $tag->slug);
    }

    #[Test]
    public function it_generates_arabic_slugs_for_categories()
    {
        $category = Category::create([
            'name' => 'قسم التكنولوجيا'
        ]);

        $this->assertEquals('قسم-التكنولوجيا', $category->slug);
    }

    #[Test]
    public function it_handles_mixed_arabic_and_english_slugs()
    {
        $tag = Tag::create([
            'name' => 'Laravel بالعربي'
        ]);

        $this->assertEquals('laravel-بالعربي', $tag->slug);
    }

    #[Test]
    public function it_ensures_unique_arabic_slugs()
    {
        Book::create([
            'title' => 'كتاب مكرر',
            'author' => 'مؤلف 1',
            'isbn' => '1111111111111'
        ]);

        $book2 = Book::create([
            'title' => 'كتاب مكرر',
            'author' => 'مؤلف 2',
            'isbn' => '2222222222222'
        ]);

        $this->assertEquals('كتاب-مكرر-1', $book2->slug);
    }
}
