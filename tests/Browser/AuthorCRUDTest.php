<?php

namespace Tests\Browser;

use App\Models\User;
use App\Models\Author;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AuthorCRUDTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test that authors index page loads
     */
    public function test_authors_index_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/authors')
                ->waitForText('المؤلف') // Wait for header or table content
                ->assertPresent('[data-page]');
        });
    }

    /**
     * Test navigation to create author page
     */
    public function test_can_navigate_to_create_author(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/authors')
                ->waitForText('إضافة مؤلف جديد')
                ->visit('/authors/create')
                ->assertPathIs('/authors/create')
                ->waitFor('#name')
                ->assertSee('إضافة مؤلف جديد')
                ->assertPresent('#name');
        });
    }

    /**
     * Test navigation to edit author page
     */
    public function test_can_navigate_to_edit_author(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create(['name' => 'Author To Edit']);

        $this->browse(function (Browser $browser) use ($user, $author) {
            $browser->loginAs($user)
                ->visit('/authors/' . $author->id . '/edit')
                ->assertPathIs('/authors/' . $author->id . '/edit')
                ->waitFor('#name')
                ->assertInputValue('#name', 'Author To Edit');
        });
    }

    /**
     * Test navigation to show author page
     */
    public function test_can_navigate_to_show_author(): void
    {
        $user = User::factory()->create();
        $author = Author::factory()->create(['name' => 'Author Details']);

        $this->browse(function (Browser $browser) use ($user, $author) {
            $browser->loginAs($user)
                ->visit('/authors/' . $author->id)
                ->waitForText('نبذة تعريفية')
                ->assertSee('Author Details');
        });
    }
}
