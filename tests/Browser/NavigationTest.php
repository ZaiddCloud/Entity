<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigationTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test navigation from dashboard to books
     */
    public function test_navigate_dashboard_to_books(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->pause(2000)
                ->visit('/books')
                ->pause(2000)
                ->assertPathIs('/books')
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test navigation to categories
     */
    public function test_navigate_to_categories(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/categories')
                ->pause(2000)
                ->assertPathIs('/categories')
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test navigation to tags
     */
    public function test_navigate_to_tags(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/tags')
                ->pause(2000)
                ->assertPathIs('/tags')
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test navigation to category details
     */
    public function test_navigate_to_category_details(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $category) {
            $browser->loginAs($user)
                ->visit('/categories/' . $category->uuid)
                ->pause(2000)
                ->assertPathIs('/categories/' . $category->uuid)
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test navigation to tag details
     */
    public function test_navigate_to_tag_details(): void
    {
        $user = User::factory()->create();
        $tag = Tag::factory()->create();

        $this->browse(function (Browser $browser) use ($user, $tag) {
            $browser->loginAs($user)
                ->visit('/tags/' . $tag->uuid)
                ->pause(2000)
                ->assertPathIs('/tags/' . $tag->uuid)
                ->assertPresent('[data-page]');
        });
    }
}
