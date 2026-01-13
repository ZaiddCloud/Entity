<?php

namespace Tests\Browser;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ActivityTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test verifying activities list is rendered
     */
    public function test_can_view_activities(): void
    {
        $user = User::factory()->create();
        $activity = Activity::factory()->create([
            'description' => 'Test Activity Description',
            'user_id' => $user->id,
            'activity_type' => 'created',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/activities')
                ->waitForText('سجل الرقابة')
                ->assertSee('Test Activity Description');
        });
    }

    /**
     * Test filtering activities by type
     */
    public function test_can_filter_activities_by_type(): void
    {
        $user = User::factory()->create();
        
        Activity::factory()->create([
            'description' => 'Created Item',
            'user_id' => $user->id,
            'activity_type' => 'created',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);

        Activity::factory()->create([
            'description' => 'Deleted Item',
            'user_id' => $user->id,
            'activity_type' => 'deleted',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/activities')
                ->pause(1000)
                ->select('#activity-type-filter select', 'created')
                ->pause(1000) // Wait for debounce/fetch
                ->assertSee('Created Item')
                ->waitUntilMissingText('Deleted Item');
        });
    }

    /**
     * Test searching activities
     */
    public function test_can_search_activities(): void
    {
        $user = User::factory()->create();
        
        Activity::factory()->create([
            'description' => 'UniqueSearchTerm',
            'user_id' => $user->id,
            'activity_type' => 'updated',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);

        Activity::factory()->create([
            'description' => 'Other Activity',
            'user_id' => $user->id,
            'activity_type' => 'updated',
            'entity_type' => User::class,
            'entity_id' => $user->id,
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->visit('/activities?search=UniqueSearchTerm')
                ->waitForText('UniqueSearchTerm') // Wait for results
                ->assertSee('UniqueSearchTerm')
                ->assertDontSee('Other Activity');
        });
    }
}
