<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PageAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_show_page_is_accessible()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->get(route('books.show', $book->slug));

        $response->assertStatus(200);
    }

    public function test_book_edit_page_is_accessible()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $response = $this->actingAs($user)->get(route('books.edit', $book->slug));

        $response->assertStatus(200);
    }

    public function test_video_show_page_is_accessible()
    {
        $user = User::factory()->create();
        $video = Video::factory()->create();

        $response = $this->actingAs($user)->get(route('videos.show', $video->slug));

        $response->assertStatus(200);
    }

    public function test_video_edit_page_is_accessible()
    {
        $user = User::factory()->create();
        $video = Video::factory()->create();

        $response = $this->actingAs($user)->get(route('videos.edit', $video->slug));

        $response->assertStatus(200);
    }
}
