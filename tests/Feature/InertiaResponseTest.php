<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Collection;
use Inertia\Testing\AssertableInertia as Assert;

class InertiaResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        User::factory()->create();
    }

    /** @test */
    public function it_renders_books_index_page()
    {
        Book::factory()->count(3)->create();
        $this->get('/books')->assertInertia(fn (Assert $page) => $page->component('Books/Index'));
    }

    /** @test */
    public function it_renders_audio_index_page()
    {
        Audio::factory()->count(3)->create();
        $this->get('/audios')->assertInertia(fn (Assert $page) => $page->component('Audio/Index'));
    }

    /** @test */
    public function it_renders_videos_index_page()
    {
        Video::factory()->count(3)->create();
        $this->get('/videos')->assertInertia(fn (Assert $page) => $page->component('Videos/Index'));
    }

    /** @test */
    public function it_renders_manuscripts_index_page()
    {
        Manuscript::factory()->count(3)->create();
        $this->get('/manuscripts')->assertInertia(fn (Assert $page) => $page->component('Manuscripts/Index'));
    }

    /** @test */
    public function it_renders_categories_index_page()
    {
        Category::factory()->count(3)->create();
        $this->get('/categories')->assertInertia(fn (Assert $page) => $page->component('Categories/Index'));
    }

    /** @test */
    public function it_renders_tags_index_page()
    {
        Tag::factory()->count(3)->create();
        $this->get('/tags')->assertInertia(fn (Assert $page) => $page->component('Tags/Index'));
    }

    /** @test */
    public function it_renders_collections_index_page()
    {
        Collection::factory()->count(3)->create();
        $response = $this->get('/collections');
        if (!$response->baseResponse instanceof \Inertia\Response) {
            // dd($response->getContent());
        }
        $response->assertInertia(fn (Assert $page) => $page->component('Collections/Index'));
    }

    /** @test */
    public function it_renders_series_index_page()
    {
        \App\Models\Series::factory()->count(3)->create();
        $this->get('/series')->assertInertia(fn (Assert $page) => $page->component('Series/Index'));
    }
}
