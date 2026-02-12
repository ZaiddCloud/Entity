<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use App\Models\AudioSegment;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioSmartSplitterTest extends DuskTestCase
{
    /**
     * Test that the Full View UI loads correctly (Smoke Test).
     * Detailed logic is tested in Feature/Studio/SmartSplitterTest.php
     */
    public function test_smart_splitter_ui_loads()
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
        
        // Ensure at least 2 segments exist for Full View
        if ($audio->children()->count() < 2) {
             \App\Models\AudioSegment::firstOrCreate(
                ['slug' => 'segment-1-sharah'],
                [
                    'audio_id' => $audio->id,
                    'title' => 'المقطع الأول',
                    'order' => 1,
                    'start_time' => 0,
                    'content' => '<p>محتوى المقطع الأول</p>'
                ]
            );
            
            \App\Models\AudioSegment::firstOrCreate(
                ['slug' => 'segment-2-sharah'],
                [
                    'audio_id' => $audio->id,
                    'title' => 'المقطع الثاني',
                    'order' => 2,
                    'start_time' => 300,
                    'content' => '<p>محتوى افتراضي للمقطع الثاني.</p>'
                ]
            );
            $audio->refresh();
        }
        
        $this->browse(function (Browser $browser) use ($user, $audio) {
            $browser->loginAs($user)
                    ->visit(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug, 'childId' => 'full']))
                    ->waitForText('شرح ألفية ابن مالك')
                    ->assertSee('كامل المحتوى') // Full View Indicator
                    ->waitFor('.ProseMirror') // Editor loaded
                    ->assertSee('المقطع الأول') // Content loaded
                    ->assertSee('المقطع الثاني')
                    ->assertVisible('#studio-save-btn'); // Save button exists
        });
    }
}
