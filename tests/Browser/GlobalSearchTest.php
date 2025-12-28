<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Book;
use App\Models\Author;
use App\Models\Series;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class GlobalSearchTest extends DuskTestCase
{
    use RefreshDatabase;

    /**
     * Test global search redirects to search page
     */
    public function test_global_search_redirects(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->type('input[placeholder*="بحث سريع"]', 'test')
                ->keys('input[placeholder*="بحث سريع"]', '{enter}')
                ->pause(500)
                ->assertPathIs('/search')
                ->assertQueryStringHas('q', 'test');
        });
    }

    /**
     * Test search results are displayed
     */
    public function test_search_results_displayed(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'Philosophy Book']);
        $author = Author::factory()->create(['name' => 'Al-Farabi']);

        $this->browse(function (Browser $browser) use ($user, $book, $author) {
            $browser->loginAs($user)
                ->visit('/search?q=Philosophy')
                ->assertPathIs('/search')
                ->assertSeeIn('body', $book->title);
        });
    }

    /**
     * Test empty search shows no results message
     */
    public function test_empty_search_page_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/search?q=nonexistent_query_12345')
                ->assertPathIs('/search')
                ->assertPresent('[data-page]');
        });
    }
}
