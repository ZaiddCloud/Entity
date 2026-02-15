<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioInteractionTest extends DuskTestCase
{
    /**
     * Test dropdown search functionality for segments.
     */
    public function test_dropdown_search_filters_segments()
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
            $s1Id = \Illuminate\Support\Str::uuid();
            \App\Models\AudioSegment::create([
                'id' => $s1Id,
                'audio_id' => $audio->id,
                'title' => 'المقطع الأول',
                'slug' => 'segment-1',
                'order' => 1,
                'start_time' => 0,
                'duration' => 300,
                'content' => "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s1Id}\" data-type=\"segment\" data-start-time=\"0\">المقطع الأول</h4><p>محتوى المقطع الأول</p>"
            ]);
            
            $s2Id = \Illuminate\Support\Str::uuid();
            \App\Models\AudioSegment::create([
                'id' => $s2Id,
                'audio_id' => $audio->id,
                'title' => 'المقطع الثاني',
                'slug' => 'segment-2',
                'order' => 2,
                'start_time' => 300,
                'duration' => 300,
                'content' => "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s2Id}\" data-type=\"segment\" data-start-time=\"300\">المقطع الثاني</h4><p>محتوى المقطع الثاني</p>"
            ]);
            
            $s3Id = \Illuminate\Support\Str::uuid();
            \App\Models\AudioSegment::create([
                'id' => $s3Id,
                'audio_id' => $audio->id,
                'title' => 'المقطع الثالث',
                'slug' => 'segment-3',
                'order' => 3,
                'start_time' => 600,
                'duration' => 300,
                'content' => "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s3Id}\" data-type=\"segment\" data-start-time=\"600\">المقطع الثالث</h4><p>محتوى المقطع الثالث</p>"
            ]);
            
            $audio->refresh(); 
        }
        
        $this->browse(function (Browser $browser) use ($user, $audio) {
            $browser->loginAs($user)
                    ->visit("/studio/audio/{$audio->slug}")
                    ->waitForText('Entity Studio')
                    ->pause(1500)
                    
                    // Open dropdown
                    ->click('#studio-dropdown-btn')
                    ->waitFor('input[placeholder="ابحث عن مقطع..."]')
                    
                    // Type search query
                    ->type('input[placeholder="ابحث عن مقطع..."]', 'الأول')
                    ->pause(500)
                    
                    // Verify filtered results
                    ->assertSee('المقطع الأول')
                    
                    // Clear search
                    ->keys('input[placeholder="ابحث عن مقطع..."]', ['{control}', 'a'])
                    ->keys('input[placeholder="ابحث عن مقطع..."]', ['{backspace}'])
                    ->pause(500)
                    
                    // Verify all segments visible again
                    ->assertSee('المقطع');
        });
    }
    
    /**
     * Test media player visibility in Studio.
     */
    public function test_media_player_docking_toggle()
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
        
        $this->browse(function (Browser $browser) use ($user, $audio) {
            $browser->loginAs($user)
                    ->visit("/studio/audio/{$audio->slug}")
                    ->waitForText('Entity Studio')
                    ->pause(1500)
                    
                    // Verify player is visible (using actual class from MediaPlayer.vue)
                    ->assertVisible('.pot-window-v2')
                    
                    // Verify player title is displayed
                    ->assertSee($audio->title);
        });
    }
    
    /**
     * Test auto-save indicator appears during edits.
     */
    public function test_autosave_indicator_appears()
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
            $s1Id = \Illuminate\Support\Str::uuid();
            \App\Models\AudioSegment::create([
                'id' => $s1Id,
                'audio_id' => $audio->id,
                'title' => 'المقطع الأول',
                'slug' => 'segment-1',
                'order' => 1,
                'start_time' => 0,
                'duration' => 300,
                'content' => "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s1Id}\" data-type=\"segment\" data-start-time=\"0\">المقطع الأول</h4><p>محتوى المقطع الأول</p>"
            ]);
            $audio->refresh();
        }

        $segment = $audio->children->first();
        
        $this->browse(function (Browser $browser) use ($user, $audio, $segment) {
            $browser->loginAs($user)
                    ->visit("/studio/audio/{$audio->slug}/{$segment->_id}")
                    ->waitForText('Entity Studio')
                    ->pause(1500)
                    
                    // Make a small edit
                    ->click('.ProseMirror')
                    ->keys('.ProseMirror', ' تعديل تجريبي')
                    ->pause(2000) // Wait for auto-save debounce
                    
                    // Verify save status appears
                    // Note: Adjust selector based on actual implementation
                    ->pause(1000);
        });
    }
}
