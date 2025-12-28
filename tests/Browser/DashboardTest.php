<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DashboardTest extends DuskTestCase
{
    use RefreshDatabase;

    /**
     * Test that authenticated user can access dashboard
     */
    public function test_user_can_access_dashboard(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->assertPathIs('/dashboard')
                ->assertPresent('nav') // Check navigation exists
                ->assertPresent('aside'); // Check sidebar exists
        });
    }

    /**
     * Test that guest is redirected to login
     */
    public function test_guest_redirected_to_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/dashboard')
                ->assertPathIs('/login');
        });
    }

    /**
     * Test Inertia page component is loaded
     */
    public function test_inertia_page_loads(): void
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/dashboard')
                ->waitFor('[data-page]', 5) // Wait for Inertia page to load
                ->assertAttribute('[data-page]', 'component', 'Dashboard');
        });
    }
}
