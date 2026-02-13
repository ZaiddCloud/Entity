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
        $uniqueId = uniqid();
        $user = User::factory()->create(['email' => 'splitter_test_' . $uniqueId . '@example.com']);
        
        // Ensure Audio exists with unique slug
        $audio = Audio::create([
            'slug' => 'sharah-alfiyya-' . $uniqueId,
            'title' => 'شرح ألفية ابن مالك ' . $uniqueId,
            'duration' => 3600,
            'format' => 'mp3',
            'description' => 'شرح وافي للألفية',
        ]);
        
        // Create 2 segments directly
        \App\Models\AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'المقطع الأول',
            'slug' => 'segment-1-' . $uniqueId,
            'order' => 1,
            'start_time' => 0,
            'content' => '<p>محتوى المقطع الأول</p>'
        ]);
        
        \App\Models\AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'المقطع الثاني',
            'slug' => 'segment-2-' . $uniqueId,
            'order' => 2,
            'start_time' => 300,
            'content' => '<p>محتوى افتراضي للمقطع الثاني.</p>'
        ]);
        
        $this->browse(function (Browser $browser) use ($user, $audio) {
            $browser->loginAs($user)
                    ->visit(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug, 'childId' => 'full']))
                    ->waitForText($audio->title)
                    ->assertSee('كامل المحتوى') // Full View Indicator
                    ->waitFor('.ProseMirror') // Editor loaded
                    ->assertSee('المقطع الأول') // Content loaded
                    ->assertSee('المقطع الثاني')
                    ->assertVisible('#studio-save-btn'); // Save button exists
        });
    }
}
