<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Author;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_guest_cannot_access_search(): void
    {
        $response = $this->get(route('search'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_access_search_page(): void
    {
        $response = $this->actingAs($this->user)->get(route('search'));
        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Search/Index')
                ->has('results')
                ->has('term')
        );
    }

    public function test_search_returns_correct_results(): void
    {
        $book = Book::factory()->create(['title' => 'Deep Learning Book']);
        $author = Author::factory()->create(['name' => 'Ian Goodfellow']);
        $series = Series::factory()->create(['title' => 'AI Series']);

        // Search for 'Deep'
        $response = $this->actingAs($this->user)->get(route('search', ['q' => 'Deep']));
        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->where('term', 'Deep')
                ->has('results.books', 1)
                ->where('results.books.0.title', 'Deep Learning Book')
                ->has('results.authors', 0)
        );

        // Search for 'Ian'
        $response = $this->actingAs($this->user)->get(route('search', ['q' => 'Ian']));
        $response->assertInertia(
            fn($page) => $page
                ->where('term', 'Ian')
                ->has('results.authors', 1)
                ->where('results.authors.0.name', 'Ian Goodfellow')
        );

        // Search for 'Series'
        $response = $this->actingAs($this->user)->get(route('search', ['q' => 'Series']));
        $response->assertInertia(
            fn($page) => $page
                ->where('term', 'Series')
                ->has('results.series', 1)
        );
    }

    public function test_search_returns_empty_for_no_term(): void
    {
        $response = $this->actingAs($this->user)->get(route('search'));
        $response->assertInertia(
            fn($page) => $page
                ->where('results', [])
                ->where('term', '')
        );
    }
}
