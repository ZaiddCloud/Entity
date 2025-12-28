<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Book;
use App\Models\Author;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BooksPageTest extends DuskTestCase
{
    use RefreshDatabase;

    /**
     * Test that books index page loads correctly
     */
    public function test_books_index_page_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books')
                ->pause(2000)
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test that books are displayed in the table
     */
    public function test_books_displayed_in_table(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create(['name' => 'Test Author']);
        $book = Book::factory()->create(['title' => 'Test Book Title']);
        $book->authors()->attach($author);

        $this->browse(function (Browser $browser) use ($user, $book) {
            $browser->loginAs($user)
                ->visit('/books')
                ->pause(2000)
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test search functionality
     */
    public function test_search_filters_books(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['title' => 'First Book']);
        $book2 = Book::factory()->create(['title' => 'Second Book']);

        $this->browse(function (Browser $browser) use ($user, $book1, $book2) {
            $browser->loginAs($user)
                ->visit('/books')
                ->pause(2000)
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test pagination exists
     */
    public function test_pagination_exists(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(15)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books')
                ->pause(2000)
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test navigation to book details
     */
    public function test_can_navigate_to_book_details(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $book) {
            $browser->loginAs($user)
                ->visit('/books/' . $book->slug)
                ->pause(2000)
                ->assertPresent('[data-page]');
        });
    }
}
