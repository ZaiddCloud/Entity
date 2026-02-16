<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use App\Models\AudioSegment;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Support\Str;

class StudioSmartSplitterTest extends DuskTestCase
{
    use DatabaseTruncation;
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
        $s1Id = (string) Str::uuid();
        $s1 = new AudioSegment();
        $s1->_id = $s1Id;
        $s1->audio_id = $audio->id;
        $s1->title = 'المقطع الأول';
        $s1->slug = 'segment-1-' . $uniqueId;
        $s1->order = 1;
        $s1->start_time = 0;
        $s1->content = '<p>محتوى المقطع الأول</p>';
        $s1->save();
        
        $s2Id = (string) Str::uuid();
        $s2 = new AudioSegment();
        $s2->_id = $s2Id;
        $s2->audio_id = $audio->id;
        $s2->title = 'المقطع الثاني';
        $s2->slug = 'segment-2-' . $uniqueId;
        $s2->order = 2;
        $s2->start_time = 300;
        $s2->content = '<p>محتوى افتراضي للمقطع الثاني.</p>';
        $s2->save();
        
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
