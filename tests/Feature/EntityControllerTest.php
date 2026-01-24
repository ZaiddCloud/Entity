<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Author;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EntityControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    /** @test */
    public function author_index_page_loads_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('authors.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Authors/Index'));
    }

    /** @test */
    public function author_create_page_loads_successfully()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('authors.create'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page->component('Authors/Create'));
    }

    /** @test */
    public function can_create_author_via_entity_controller()
    {
        $this->actingAs($this->user);

        $data = [
            'name' => 'Test Author',
            'bio' => 'Test bio',
            'birth_year' => 1900,
            'death_year' => 2000,
        ];

        $response = $this->post(route('authors.store'), $data);

        $response->assertRedirect(route('authors.index'));
        $this->assertDatabaseHas('authors', ['name' => 'Test Author']);
    }

    /** @test */
    public function can_update_author_via_entity_controller()
    {
        $this->actingAs($this->user);

        $author = Author::factory()->create(['name' => 'Old Name']);

        $data = [
            'name' => 'New Name',
            'bio' => 'Updated bio',
            'birth_year' => 1900,
            'death_year' => 2000,
        ];

        $response = $this->put(route('authors.update', $author), $data);

        if (session('errors')) {
            dump(session('errors')->all());
        }

        $response->assertRedirect(route('authors.show', $author));
        $this->assertDatabaseHas('authors', ['name' => 'New Name']);
    }

    /** @test */
    public function can_delete_author_via_entity_controller()
    {
        $this->actingAs($this->user);

        $author = Author::factory()->create();

        $response = $this->delete(route('authors.destroy', $author));

        $response->assertRedirect(route('authors.index'));
        $this->assertSoftDeleted('authors', ['id' => $author->id]);
    }

    /** @test */
    public function can_restore_author_via_entity_controller()
    {
        $this->actingAs($this->user);
        $author = Author::factory()->create();
        $author->delete();

        $response = $this->post(route('authors.restore', $author));

        $response->assertRedirect(route('authors.index'));
        $this->assertDatabaseHas('authors', ['id' => $author->id, 'deleted_at' => null]);
    }

    /** @test */
    public function can_force_delete_author_via_entity_controller()
    {
        $this->actingAs($this->user);
        $author = Author::factory()->create();
        
        $response = $this->delete(route('authors.force-delete', $author));
        
        $response->assertRedirect(route('authors.index'));
        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    /** @test */
    public function search_functionality_works()
    {
        $this->actingAs($this->user);

        Author::factory()->create(['name' => 'John Doe']);
        Author::factory()->create(['name' => 'Jane Smith']);

        $response = $this->get(route('authors.index', ['search' => 'John']));

        $response->assertStatus(200);
        // We verify it loads correctly. Testing the exact props would require more detailed assertion.
    }
}
