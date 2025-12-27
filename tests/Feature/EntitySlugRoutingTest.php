<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntitySlugRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function book_url_contains_slug_instead_of_uuid()
    {
        $book = Book::factory()->create(['title' => 'My Test Book']);
        $url = route('books.show', $book);
        
        $this->assertStringContainsString('my-test-book', $url);
        $this->assertStringNotContainsString($book->id, $url);

        $this->actingAs($this->user)
            ->get($url)
            ->assertOk()
            ->assertSee('My Test Book');
    }

    /** @test */
    public function audio_url_contains_slug_instead_of_uuid()
    {
        $audio = Audio::factory()->create(['title' => 'My Test Audio']);
        $url = route('audios.show', $audio);
        
        $this->assertStringContainsString('my-test-audio', $url);
        $this->assertStringNotContainsString($audio->id, $url);

        $this->actingAs($this->user)
            ->get($url)
            ->assertOk()
            ->assertSee('My Test Audio');
    }

    /** @test */
    public function video_url_contains_slug_instead_of_uuid()
    {
        $video = Video::factory()->create(['title' => 'My Test Video']);
        $url = route('videos.show', $video);
        
        $this->assertStringContainsString('my-test-video', $url);
        $this->assertStringNotContainsString($video->id, $url);

        $this->actingAs($this->user)
            ->get($url)
            ->assertOk()
            ->assertSee('My Test Video');
    }

    /** @test */
    public function manuscript_url_contains_slug_instead_of_uuid()
    {
        $manuscript = Manuscript::factory()->create(['title' => 'My Test Manuscript']);
        $url = route('manuscripts.show', $manuscript);
        
        $this->assertStringContainsString('my-test-manuscript', $url);
        $this->assertStringNotContainsString($manuscript->id, $url);

        $this->actingAs($this->user)
            ->get($url)
            ->assertOk()
            ->assertSee('My Test Manuscript');
    }

    /** @test */
    public function slug_is_automatically_generated_on_creation()
    {
        $book = Book::create([
            'title' => 'Automatic Slug Test',
            // Other fields are optional or handled by factory-like logic in some setups, 
            // but here we rely on the Entity boot logic.
        ]);

        $this->assertEquals('automatic-slug-test', $book->slug);
    }

    /** @test */
    public function slug_is_automatically_updated_when_title_changes()
    {
        $book = Book::factory()->create(['title' => 'Old Title']);
        $this->assertEquals('old-title', $book->slug);

        $book->update(['title' => 'New Awesome Title']);
        $this->assertEquals('new-awesome-title', $book->slug);
    }
}
