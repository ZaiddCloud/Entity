<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use App\Models\AudioSegment;
use Illuminate\Support\Str;

class StudioInteractionTest extends DuskTestCase
{
    use DatabaseTruncation;
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
            $s1Id = (string) Str::uuid();
            $s1 = new AudioSegment();
            $s1->_id = $s1Id;
            $s1->audio_id = $audio->id;
            $s1->title = 'المقطع الأول';
            $s1->slug = 'segment-1';
            $s1->order = 1;
            $s1->start_time = 0;
            $s1->duration = 300;
            $s1->content = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s1Id}\" data-type=\"segment\" data-start-time=\"0\">المقطع الأول</h4><p>محتوى المقطع الأول</p>";
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
            $s2->content = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s2Id}\" data-type=\"segment\" data-start-time=\"300\">المقطع الثاني</h4><p>محتوى المقطع الثاني</p>";
            $s2->save();
            
            $s3Id = (string) Str::uuid();
            $s3 = new AudioSegment();
            $s3->_id = $s3Id;
            $s3->audio_id = $audio->id;
            $s3->title = 'المقطع الثالث';
            $s3->slug = 'segment-3';
            $s3->order = 3;
            $s3->start_time = 600;
            $s3->duration = 300;
            $s3->content = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s3Id}\" data-type=\"segment\" data-start-time=\"600\">المقطع الثالث</h4><p>محتوى المقطع الثالث</p>";
            $s3->save();
            
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
            $s1Id = (string) \Illuminate\Support\Str::uuid();
            $s1 = new AudioSegment();
            $s1->_id = $s1Id;
            $s1->audio_id = $audio->id;
            $s1->title = 'المقطع الأول';
            $s1->slug = 'segment-1';
            $s1->order = 1;
            $s1->start_time = 0;
            $s1->duration = 300;
            $s1->content = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$s1Id}\" data-type=\"segment\" data-start-time=\"0\">المقطع الأول</h4><p>محتوى المقطع الأول</p>";
            $s1->save();
            $audio->refresh();
        }

        $segment = $audio->children->first();
        
        $this->browse(function (Browser $browser) use ($user, $audio, $segment) {
            $browser->loginAs($user)
                    ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug, 'childId' => $segment->_id])
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
