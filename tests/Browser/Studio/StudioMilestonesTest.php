<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use App\Models\AudioSegment;
use App\Enums\ContentNodeType;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class StudioMilestonesTest extends DuskTestCase
{
    use DatabaseTruncation;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test Milestone #1 & #3: Modular Toolbar & Scientific Tools
     */
    public function test_toolbar_has_modular_groups_and_scientific_tools(): void
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create(['title' => 'Milestone Audio']);
        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Segment 1',
            'slug' => 'segment-1',
            'content' => '<p>Content</p>',
            'order' => 1,
            'type' => ContentNodeType::SEGMENT->value
        ]);

        $this->browse(function (Browser $browser) use ($user, $audio) {
            $this->authenticateUser($browser, $user);
            
            $browser->visit("/studio/audio/{$audio->slug}")
                ->waitFor('header h1', 60)
                ->assertSeeIn('header h1', $audio->title)
                
                // Screenshot of initial load
                ->screenshot('milestone-initial-load')
                
                // Check for modular groups
                ->waitFor('@history-group', 20)
                ->assertVisible('@history-group')
                ->assertVisible('@structure-group')
                ->assertVisible('@formatting-group')
                ->assertVisible('@scientific-group')
                ->assertVisible('@heritage-group')
                
                // Verify specific tools in groups
                ->assertVisible('@insert-heritage-poetry-button')
                ->assertVisible('@insert-quranic-verse-button')
                ->assertVisible('@insert-scientific-footnote-button');
        });
    }

    /**
     * Test Milestone #2, #7, #8, #10: Player Docking, UX & Mirrored Layout
     */
    public function test_player_docking_and_mirrored_layout(): void
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create(['title' => 'Docking Audio']);
        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Segment 1',
            'slug' => 'segment-1',
            'content' => '<p>Content</p>',
            'order' => 1,
            'type' => ContentNodeType::SEGMENT->value
        ]);

        $this->browse(function (Browser $browser) use ($user, $audio) {
            $this->authenticateUser($browser, $user);
            
            \Log::info('Test: test_player_docking_and_mirrored_layout - Authenticated');
            
            $browser->visit("/studio/audio/{$audio->slug}")
                ->screenshot('milestone-studio-visit')
                ->waitFor('@media-player', 60) // Increased timeout
                ->waitFor('@tiptap-editor', 60) // Ensure editor also loads
                ->screenshot('milestone-studio-loaded')
                
                // Verify Mirrored Layout (Controls on right visually in RTL)
                ->assertPresent('.player-header .header-controls')
                
                // Test Undocking
                ->click('@toggle-dock-button')
                ->pause(2000)
                // Use a script to verify computed style as 'style' attribute might be reactive/complex
                ->assertScript("window.getComputedStyle(document.querySelector('[dusk=\"media-player\"]')).position === 'fixed'")
                
                // Test Docking back
                ->click('@toggle-dock-button')
                ->pause(2000)
                ->assertScript("window.getComputedStyle(document.querySelector('[dusk=\"media-player\"]')).position !== 'fixed'") 
                
                // Test Close & Restore
                ->waitFor('@close-player-button', 10)
                ->click('@close-player-button')
                ->waitUntilMissing('@media-player', 10);
                // Restore logic is usually in a side menu or auto-restore on route change,
                // for now we just verify it closes.
        });
    }

    /**
     * Test Milestone #17, #22: Interactive Sync & Timeline Visualization
     */
    public function test_editor_player_interactive_sync(): void
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create(['title' => 'Sync Audio']);
        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Segment 1',
            'slug' => 'segment-1',
            'content' => '<p>Link to <span class="segment-link" data-start="10">Match</span></p>',
            'order' => 1,
            'type' => ContentNodeType::SEGMENT->value,
            'start_time' => 10
        ]);

        $this->browse(function (Browser $browser) use ($user, $audio) {
            $this->authenticateUser($browser, $user);
            
            $browser->visit("/studio/audio/{$audio->slug}")
                ->waitFor('@tiptap-editor', 60)
                
                // Verify segment marker on timeline
                ->waitFor('@segment-marker-segment-1', 10)
                ->assertVisible('@segment-marker-segment-1')
                
                // Click segment link in editor (using XPath to ensure we hit the link text)
                ->clickAtXPath("//span[contains(@class, 'segment-link') and contains(text(), 'Match')]")
                ->pause(3000)
                // Verify seek happened
                ->waitForText('0:10', 30)
                ->assertSeeIn('@current-time-display', '0:10'); 
        });
    }

    /**
     * Test Milestone #23: Smart Save (Optimistic UI)
     */
    public function test_smart_save_persistence(): void
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create(['title' => 'Save Audio']);
        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Segment 1',
            'slug' => 'segment-1',
            'content' => '<p>Old Content</p>',
            'order' => 1,
            'type' => ContentNodeType::SEGMENT->value
        ]);

        $this->browse(function (Browser $browser) use ($user, $audio) {
            $this->authenticateUser($browser, $user);
            
            $browser->visit("/studio/audio/{$audio->slug}")
                ->waitFor('@tiptap-editor', 60)
                
                // Edit content targeting ProseMirror div
                ->click('.ProseMirror')
                ->keys('New Augmented Content')
                ->pause(2000)
                ->waitFor('@save-button', 10)
                ->click('@save-button')
                
                // Check notification or status (Arabic for "Saved")
                ->waitForText('تم الحفظ', 15);
        });
    }
}
