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
     * Test global search functionality
     */
    public function test_global_search_works(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'البحث عن الحقيقة']);
        $author = Author::factory()->create(['name' => 'محمد عبده']);
        $series = Series::factory()->create(['title' => 'سلسلة الفلسفة']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->type('input[placeholder*="بحث سريع"]', 'الحقيقة')
                ->keys('input[placeholder*="بحث سريع"]', '{enter}')
                ->pause(500)
                ->assertPathIs('/search')
                ->assertSee('البحث عن الحقيقة');
        });
    }

    /**
     * Test search results grouping
     */
    public function test_search_results_grouped_by_type(): void
    {
        $user = User::factory()->create();
        Book::factory()->create(['title' => 'كتاب الفلسفة']);
        Author::factory()->create(['name' => 'الفارابي']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/search?q=الفلسفة')
                ->assertSee('الكتب')
                ->assertSee('المؤلفون')
                ->assertSee('كتاب الفلسفة')
                ->assertSee('الفارابي');
        });
    }

    /**
     * Test empty search results
     */
    public function test_empty_search_shows_message(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/search?q=نتيجة_غير_موجودة_أبداً')
                ->assertSee('لم يتم العثور على نتائج');
        });
    }
}
