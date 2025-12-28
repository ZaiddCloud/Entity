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
                ->assertSee('إدارة الكتب')
                ->assertSee('إضافة كتاب جديد');
        });
    }

    /**
     * Test that books are displayed in the table
     */
    public function test_books_displayed_in_table(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create(['name' => 'أحمد شوقي']);
        $book = Book::factory()->create(['title' => 'ديوان الشوقيات']);
        $book->authors()->attach($author);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books')
                ->assertSee('ديوان الشوقيات')
                ->assertSee('أحمد شوقي');
        });
    }

    /**
     * Test search functionality
     */
    public function test_search_filters_books(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['title' => 'كتاب الأول']);
        Book::factory()->create(['title' => 'كتاب الثاني']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/books')
                ->type('input[placeholder*="بحث"]', 'الأول')
                ->pause(1000) // Wait for debounce
                ->assertSee('كتاب الأول')
                ->assertDontSee('كتاب الثاني');
        });
    }

    /**
     * Test navigation to book details
     */
    public function test_can_navigate_to_book_details(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'كتاب تجريبي']);

        $this->browse(function (Browser $browser) use ($user, $book) {
            $browser->loginAs($user)
                ->visit('/books')
                ->clickLink('عرض')
                ->assertPathIs('/books/' . $book->slug)
                ->assertSee('كتاب تجريبي');
        });
    }
}
