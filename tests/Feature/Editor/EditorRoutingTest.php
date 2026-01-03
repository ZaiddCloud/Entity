<?php

namespace Tests\Feature\Editor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Book;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditorRoutingTest extends TestCase
{
    use RefreshDatabase;

    public function test_editor_opens_in_book_mode()
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        // Assuming a child/chapter exists
        $child = $book->children()->create(['title' => 'First Chapter']);

        $response = $this->actingAs($user)
            ->get(route('books.editor', ['book' => $book->slug, 'child' => $child->slug]));

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Books/Editor/EditorPage')
                ->where('editor_mode', 'book')
        );
    }

    public function test_editor_opens_in_manuscript_mode_with_resource()
    {
        $user = User::factory()->create();
        $manuscript = Manuscript::factory()->create(); // Requires Manuscript model factory
        // Assuming manuscript has children/pages
        $child = $manuscript->children()->create(['title' => 'Page 1']);

        // Assuming route for manuscript editor
        $response = $this->actingAs($user)
            // Using a hypothetical route for now, will implement controller next
            ->get("/manuscripts/{$manuscript->slug}/editor/{$child->slug}");

        $response->assertStatus(200);
        $response->assertInertia(
            fn($page) => $page
                ->component('Books/Editor/EditorPage')
                ->where('editor_mode', 'manuscript')
                ->has('resource_data')
        );
    }

    public function test_editor_opens_in_media_mode_with_resource()
    {
        $this->markTestIncomplete('Media entities logic not yet defined');
    }
}
