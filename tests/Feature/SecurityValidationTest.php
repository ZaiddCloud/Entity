<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_cannot_access_books()
    {
        $response = $this->get(route('books.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_guests_cannot_create_book()
    {
        $bookData = [
            'title' => 'Test Book',
            'type' => 'book'
        ];

        $response = $this->post(route('books.store'), $bookData);

        $response->assertRedirect(route('login')); // Middleware check
    }

    public function test_authenticated_user_can_create_book()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => 'New Book',
            // type matches route inference in FormRequest
        ]);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('books', ['title' => 'New Book']);
    }

    public function test_validation_fails_for_empty_title()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('books.store'), [
            'title' => '',
        ]);

        $response->assertSessionHasErrors('title');
    }

    public function test_authenticated_user_can_create_video()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('videos.store'), [
            'title' => 'New Video',
        ]);

        $response->assertRedirect(route('videos.index'));
        $this->assertDatabaseHas('videos', ['title' => 'New Video']);
    }

    public function test_authenticated_user_can_create_audio()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('audios.store'), [
            'title' => 'New Audio',
        ]);

        $response->assertRedirect(route('audios.index'));
        $this->assertDatabaseHas('audios', ['title' => 'New Audio']);
    }

    public function test_authenticated_user_can_create_manuscript()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('manuscripts.store'), [
            'title' => 'New Manuscript',
        ]);

        $response->assertRedirect(route('manuscripts.index'));
        $this->assertDatabaseHas('manuscripts', ['title' => 'New Manuscript']);
    }
}
