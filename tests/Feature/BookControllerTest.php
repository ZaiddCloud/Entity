<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Storage::fake('public');
    }

    /** @test */
    public function book_index_page_loads_successfully()
    {
        Book::factory()->count(3)->create();

        $response = $this->get(route('books.index'));
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Books/Index')
            ->has('books.data', 3)
        );
    }

    /** @test */
    public function can_search_books_by_title_or_author()
    {
        $author = \App\Models\Author::factory()->create(['name' => 'Specific Author']);
        $book1 = Book::factory()->create(['title' => 'Specific Title']);
        $book2 = Book::factory()->create(['title' => 'Another Book']);
        $book2->authors()->attach($author);

        // Search by title
        $response = $this->get(route('books.index', ['search' => 'Specific Title']));
        $response->assertInertia(fn ($page) => $page
            ->has('books.data', 1)
            ->where('books.data.0.id', $book1->id)
        );

        // Search by author
        $response = $this->get(route('books.index', ['search' => 'Specific Author']));
        $response->assertInertia(fn ($page) => $page
            ->has('books.data', 1)
            ->where('books.data.0.id', $book2->id)
        );
    }

    /** @test */
    public function can_create_book_with_file()
    {
        $file = UploadedFile::fake()->create('book.pdf', 100);
        $cover = UploadedFile::fake()->image('cover.jpg');

        $data = [
            'title' => 'New Book',
            'file' => $file,
            'cover' => $cover,
            'description' => 'Test Description',
            // Add other required fields if any (Entity defaults handle most)
        ];

        $response = $this->post(route('books.store'), $data);

        $response->assertRedirect(route('books.index'));
        $this->assertDatabaseHas('books', ['title' => 'New Book']);
        
        // Assert storage
        // Note: Exact path depends on MediaManager logic, usually defaults to books/hash.pdf or similar
        // We just check if files were stored generally
        Storage::disk('public')->assertExists("books/" . $file->hashName());
        Storage::disk('public')->assertExists("covers/" . $cover->hashName());
    }

    /** @test */
    public function can_update_book()
    {
        $book = Book::factory()->create();
        $newFile = UploadedFile::fake()->create('updated.pdf', 100);

        $data = [
            'title' => 'Updated Title',
            'file' => $newFile,
        ];

        $response = $this->put(route('books.update', $book), $data);

        $book->refresh();
        $response->assertRedirect(route('books.show', $book));
        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Updated Title']);
        Storage::disk('public')->assertExists("books/" . $newFile->hashName());
    }

    /** @test */
    public function can_delete_book()
    {
        $book = Book::factory()->create();

        $response = $this->delete(route('books.destroy', $book));

        $response->assertRedirect(route('books.index'));
        $this->assertSoftDeleted('books', ['id' => $book->id]);
    }
}
