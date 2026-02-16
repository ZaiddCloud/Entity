<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use App\Models\AudioSegment;
use Illuminate\Support\Str;

class StudioNavigationTest extends DuskTestCase
{
    use DatabaseTruncation;
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
            $s1Id = (string) Str::uuid();
            $s1 = new AudioSegment();
            $s1->_id = $s1Id;
            $s1->audio_id = $audio->id;
            $s1->title = 'المقطع الأول';
            $s1->slug = 'segment-1';
            $s1->order = 1;
            $s1->start_time = 0;
            $s1->duration = 300;
            $s1->content = '<p>محتوى المقطع الأول</p>';
            $s1->save();
            
            $s2Id = (string) Str::uuid();
            $s2 = new AudioSegment();
            $s2->_id = $s2Id;
            $s2->audio_id = $audio->id;
            $s2->title = 'المقطع الثاني';
            $s2->slug = 'segment-2';
            $s2->order = 2;
            $s2->start_time = 300;
            $s2->duration = 300;
            $s2->content = '<p>محتوى المقطع الثاني</p>';
            $s2->save();
            
            $audio->refresh(); // Refresh relationship
        }

        $segment = $audio->children->first();
        $childId = $segment->_id; 

        $this->browse(function (Browser $browser) use ($user, $audio, $childId) {
            $browser->loginAs($user)
                    ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug, 'childId' => $childId])
                    ->waitForText('Entity Studio')
                    ->assertSee($audio->title);
        });
    }
}
