<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_book_with_files()
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $cover = UploadedFile::fake()->image('cover.jpg');
        $pdf = UploadedFile::fake()->create('book.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->post(route('books.store'), [
            'type' => 'book',
            'title' => 'Comprehensive Book',
            'author' => 'Test Author',
            'description' => 'A detailed description of the book.',
            'cover' => $cover,
            'file' => $pdf,
        ]);

        $response->assertRedirect(route('books.index'));
        
        $this->assertDatabaseHas('books', [
            'title' => 'Comprehensive Book',
            'author' => 'Test Author',
            'description' => 'A detailed description of the book.',
        ]);

        // Get the book to check paths
        $book = Book::where('title', 'Comprehensive Book')->first();
        $this->assertNotNull($book->cover_path);
        $this->assertNotNull($book->file_path);

        // Verify Storage
        Storage::disk('public')->assertExists($book->cover_path);
        Storage::disk('public')->assertExists($book->file_path);
    }

    public function test_authenticated_user_can_update_book_with_files()
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'Old Title', 'author' => 'Old Author']);

        $newCover = UploadedFile::fake()->image('new_cover.jpg');

        $response = $this->actingAs($user)->put(route('books.update', $book), [
            'title' => 'Updated Title',
            'cover' => $newCover,
        ]);
        $book->refresh();
        $response->assertRedirect(route('books.show', $book));
        
        $book->refresh();
        $this->assertEquals('Updated Title', $book->title);
        $this->assertNotNull($book->cover_path);
        Storage::disk('public')->assertExists($book->cover_path);
    }
}
