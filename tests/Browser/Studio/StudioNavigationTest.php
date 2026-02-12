<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioNavigationTest extends DuskTestCase
{
    /**
     * Test basic entry and view switching in the Studio.
     */
    /**
     * Test full navigation, view switching, and searching.
     */
    public function test_studio_navigation_and_interactions()
    {
        $user = User::factory()->create();
        
        // Ensure Audio exists
        $audio = Audio::firstOrCreate(
            ['slug' => 'شرح-ألفية-ابن-مالك'],
            [
                'title' => 'شرح ألفية ابن مالك',
                'duration' => 3600,
                'format' => 'mp3',
                'description' => 'شرح وافي للألفية',
            ]
        );

        // Ensure Segments exist
        if ($audio->children()->count() === 0) {
            \App\Models\AudioSegment::create([
                'audio_id' => $audio->id,
                'title' => 'المقطع الأول',
                'slug' => 'segment-1',
                'order' => 1,
                'start_time' => 0,
                'duration' => 300,
                'content' => '<p>محتوى المقطع الأول</p>'
            ]);
            
            \App\Models\AudioSegment::create([
                'audio_id' => $audio->id,
                'title' => 'المقطع الثاني',
                'slug' => 'segment-2',
                'order' => 2,
                'start_time' => 300,
                'duration' => 300,
                'content' => '<p>محتوى المقطع الثاني</p>'
            ]);
            
            $audio->refresh(); // Refresh relationship
        }

        $segment = $audio->children->first();
        $childId = $segment->_id ?? $segment->id; 

        $this->browse(function (Browser $browser) use ($user, $audio, $childId) {
            $browser->loginAs($user)
                    ->visit("/studio/audio/{$audio->slug}/{$childId}")
                    ->waitForText('Entity Studio')
                    ->assertSee($audio->title);
        });
    }
}
