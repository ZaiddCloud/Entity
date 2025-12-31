<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Book;
use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BooksPageTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that books index page loads correctly
     */
    public function test_books_index_page_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books')
                ->waitForText('المكتبة')
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test that books are displayed in the table
     */
    public function test_books_displayed_in_table(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create(['name' => 'Unique Author Name']);
        $book = Book::factory()->create(['title' => 'Unique Book Title']);
        $book->authors()->attach($author);

        $this->browse(function (Browser $browser) use ($user, $book) {
            $browser->loginAs($user)
                ->visit('/books')
                ->waitForText('Unique Book Title')
                ->assertSee('Unique Book Title')
                ->assertSee('Unique Author Name');
        });
    }

    /**
     * Test search functionality
     */
    public function test_search_filters_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['title' => 'Target Book']);
        Book::factory()->create(['title' => 'Ignored Book']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books?search=Target') // Direct URL search to be robust
                ->waitForText('Target Book')
                ->assertSee('Target Book')
                ->assertDontSee('Ignored Book');
        });
    }

    /**
     * Test pagination exists
     */
    public function test_pagination_exists(): void
    {
        $user = User::factory()->create();
        Book::factory()->count(50)->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books')
                ->waitFor('.flex.flex-wrap')
                ->assertPresent('.flex.flex-wrap'); // Matches pagination component class
        });
    }

    /**
     * Test navigation to book details
     */
    public function test_can_navigate_to_book_details(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'Details Book']);

        $this->browse(function (Browser $browser) use ($user, $book) {
            $browser->loginAs($user)
                ->visit('/books/' . $book->slug)
                ->waitForText('Details Book')
                ->assertSee('Details Book');
        });
    }
}
