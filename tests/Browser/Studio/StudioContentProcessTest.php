<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Book;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Step 2: The Orchestrator
 * 
 * Verifies that the useStudioContentProcess composable orchestrates 
 * node addition correctly across Editor, Player, and Backend.
 */
class StudioContentProcessTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_verifies_orchestrator_execution_for_books()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(["email" => "test_" . uniqid() . "@example.com"]);
            $book = Book::factory()->create(['title' => 'Test Book ' . uniqid()]);
            
            \App\Models\BookChild::create([
                'book_id' => $book->id,
                'title' => 'Chapter 1',
                'slug' => 'chapter-1',
                'order' => 1,
                'content' => '<p>Initial Content</p>'
            ]);

            $title = 'Test Chapter ' . uniqid();
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'book', 'slug' => $book->slug]))
                ->waitFor('.tiptap-editor', 20)
                ->assertVisible('@studio-add-button') 
                ->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->waitFor('@type-option-chapter')
                ->click('@type-option-chapter')
                ->pause(1000)
                ->waitFor('@studio-add-submit')
                ->type('@node-title-input', $title)
                ->pause(500)
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitForText($title, 15)
                ->assertSee($title)
                ->assertDontSee($title . ':');
        });
    }

    /**
     * @test
     */
    public function it_verifies_orchestrator_execution_for_manuscripts()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(["email" => "test_" . uniqid() . "@example.com"]);
            $manuscript = \App\Models\Manuscript::factory()->create();
            
            \App\Models\ManuscriptPage::create([
                'manuscript_id' => $manuscript->id,
                'title' => 'Page 1',
                'slug' => 'p1',
                'order' => 1,
                'content' => '<p>Page 1 content</p>'
            ]);

            $title = 'Folio ' . rand(1000, 9999);
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'manuscript', 'slug' => $manuscript->slug]))
                ->waitFor('.tiptap-editor', 20)
                ->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->waitFor('@type-option-folio')
                ->click('@type-option-folio')
                ->pause(1000)
                ->waitFor('@studio-add-submit')
                ->keys('@node-title-input', ['{control}', 'a'], '{backspace}')
                ->type('@node-title-input', $title)
                ->pause(500)
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitForText($title, 15)
                ->pause(0);
        });
    }

    /**
     * @test
     */
    public function it_verifies_orchestrator_execution_for_audio()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(["email" => "test_" . uniqid() . "@example.com"]);
            $audio = \App\Models\Audio::factory()->create();
            
            \App\Models\AudioSegment::create([
                'audio_id' => $audio->id,
                'title' => 'Segment 1',
                'slug' => 's1',
                'order' => 1,
                'content' => '<p>Audio 1 content</p>'
            ]);

            $title = 'Audio Seg ' . rand(1000, 9999);
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug]))
                ->waitFor('.tiptap-editor', 20)
                ->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->waitFor('@type-option-segment')
                ->click('@type-option-segment')
                ->pause(1000)
                ->waitFor('@studio-add-submit')
                ->type('@node-title-input', $title)
                ->pause(500)
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitForText($title, 15)
                ->pause(0);
        });
    }

    /**
     * @test
     */
    public function it_verifies_orchestrator_execution_for_video()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(["email" => "test_" . uniqid() . "@example.com"]);
            $video = \App\Models\Video::factory()->create();
            
            \App\Models\VideoSegment::create([
                'video_id' => $video->id,
                'title' => 'Scene 1',
                'slug' => 'sc1',
                'order' => 1,
                'content' => '<p>Video 1 content</p>'
            ]);

            $title = 'Video Scn ' . rand(1000, 9999);
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'video', 'slug' => $video->slug]))
                ->waitFor('.tiptap-editor', 20)
                ->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->waitFor('@type-option-scene')
                ->click('@type-option-scene')
                ->pause(1000)
                ->waitFor('@studio-add-submit')
                ->type('@node-title-input', $title)
                ->pause(500)
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitForText($title, 15)
                ->pause(0);
        });
    }

    /**
     * @test
     */
    public function it_allows_manual_time_entry_for_media()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(["email" => "time_test_" . uniqid() . "@example.com"]);
            $audio = \App\Models\Audio::factory()->create();
            
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug]))
                ->waitFor('.tiptap-editor', 20);

            // 1. Simulate Player Time (via JS)
            $browser->script("window.dispatchEvent(new CustomEvent('test:set-time', { detail: 65 }));");
            // Note: Since we can't easily access the store directly from outside without exposing it, 
            // we might rely on the fact that existing logic pulls from store. 
            // BUT, for this test to work *before* implementation, we need a way to mock the time or just check the input existence.
            // Let's assume we implement the JS expose in the layout or just check the input appears.
            
            // Actually, we can just check if the input exists and works.
            $browser->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->click('@type-option-segment')
                ->pause(500)
                ->assertVisible('@node-time-input') // Expecting this new input
                ->type('@node-time-input', '02:00') // Change time manually (Formatted)
                ->type('@node-title-input', 'Manual Time Segment')
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitForText('Manual Time Segment')
                ->pause(1000); // Wait for optimistic UI
                
            // Verify backend persistence (optional, or check UI reflection if possible)
            // For now, UI presence of input and successful submission is the goal.
        });
    }

    /**
     * @test
     */
    public function it_verifies_chronological_sorting()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create(["email" => "sort_test_" . uniqid() . "@example.com"]);
            $audio = \App\Models\Audio::factory()->create();
            
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug]))
                ->waitFor('.tiptap-editor', 20);

            // 1. Add Segment B at 02:00
            $browser->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->click('@type-option-segment')
                ->pause(500)
                ->type('@node-time-input', '02:00')
                ->type('@node-title-input', 'Segment B (Later)')
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitFor('.tiptap-editor', 20);

            // 2. Add Segment A at 01:00 (Out of order)
            $browser->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->click('@type-option-segment')
                ->pause(500)
                ->type('@node-time-input', '01:00')
                ->type('@node-title-input', 'Segment A (Earlier)')
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                ->waitFor('.tiptap-editor', 20);

            // 3. Open Dropdown and verify chronological order
            $browser->waitFor('#studio-dropdown-btn', 10)
                ->click('#studio-dropdown-btn')
                ->waitForText('Segment A (Earlier)')
                ->waitForText('Segment B (Later)')
                ->script(<<<'JS'
                    const items = Array.from(document.querySelectorAll('[dusk^="node-item-"]')).map(el => el.textContent.trim());
                    const indexA = items.findIndex(t => t.includes('Segment A'));
                    const indexB = items.findIndex(t => t.includes('Segment B'));
                    if (indexA === -1 || indexB === -1 || indexA > indexB) {
                        throw new Error(`Chronological Order Failed: Segment A (${indexA}) must be before Segment B (${indexB}). Items: ${items.join('|')}`);
                    }
JS
                );
        });
    }

    /**
     * @test
     */
    public function it_prevents_time_exceeding_duration()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            
            // Create audio for test
            $audio = \App\Models\Audio::factory()->create([
                'title' => 'Duration Test Audio',
                'duration' => 3600 // 1 hour
            ]);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('@studio-add-button', 20)
                ->click('@studio-add-button')
                ->waitFor('@type-option-segment')
                ->click('@type-option-segment')
                ->waitFor('@node-time-input')
                // 3601 seconds = 01:00:01
                ->type('@node-time-input', '01:00:01')
                ->waitForText('يتجاوز مدة الملف')
                ->screenshot('duration-validation-error')
                ->assertSee('يتجاوز مدة الملف')
                ->assertDisabled('@studio-add-submit');
        });
    }

    /**
     * @test
     * المرحلة الأولى: بناء الاختبار الفاشل (TDD)
     */
    public function it_prevents_bypass_using_live_duration()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'admin@admin.com')->first() 
                ?? User::factory()->create(['email' => 'admin@admin.com']);
            
            // Create Audio with Mismatch: DB says 2105s, File is shorter (sample-1.mp3)
            $slug = 'bypass-test-audio-' . uniqid();
            $audio = \App\Models\Audio::factory()->create([
                'slug' => $slug,
                'title' => 'Bypass Test',
                'duration' => 2105,
                'file_path' => 'audio/sample-1.mp3'
            ]);
            
            // "شرح ألفية ابن مالك" has duration 2105 (35:05) in DB seeder
            // BUT the actual file is 6:12 (372s). 
            // This test verifies that we cannot add at 07:00 (420s).
            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('@studio-add-button', 20)
                ->click('@studio-add-button')
                ->waitFor('@type-option-segment')
                ->click('@type-option-segment')
                ->waitFor('@node-time-input')
                // 07:00 (420s) > 6:12 (372s)
                ->type('@node-time-input', '07:00')
                // THIS SHOULD FAIL (wait timeout) because the code currently allows it
                // We expect to see the warning text "الوقت يتجاوز مدة الملف"
                ->waitForText('الوقت يتجاوز مدة الملف', 15)
                ->assertSee('الوقت يتجاوز مدة الملف')
                ->assertDisabled('@studio-add-submit');
        });
    }

    /**
     * @test
     */
    public function it_remains_on_full_content_after_adding_node()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $audio = \App\Models\Audio::factory()->create();
            
            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.tiptap-editor', 20)
                // 1. Ensure we start on "Full View"
                ->assertVisible('#studio-full-view-btn')
                ->assertSourceHas('bg-amber-500/10 text-amber-500 font-bold') // Style for active full view
                
                // 2. Add a segment
                ->click('@studio-add-button')
                ->waitFor('@type-option-segment')
                ->click('@type-option-segment')
                ->waitFor('@node-title-input')
                ->type('@node-title-input', 'Stable View Test')
                ->type('@node-time-input', '00:10')
                ->click('@studio-add-submit')
                ->waitUntilMissing('@studio-add-dropdown')
                
                // 3. Verify we are STILL on Full View (No redirect, No segment activation)
                ->pause(2000) // Wait for Inertia reload
                // check that path roughly matches (ignoring encoding) or simply check we are not on a sub-route
                ->assertUrlIs(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug]))
                ->assertQueryStringMissing('childId')
                ->assertVisible('#studio-full-view-btn')
                ->assertSourceHas('bg-amber-500/10 text-amber-500 font-bold')
                ->assertSee('Stable View Test');
        });
    }

    /**
     * @test
     */
    public function it_closes_dropdown_when_clicking_outside()
    {
        $this->browse(function (Browser $browser) {
            $user = User::factory()->create();
            $audio = \App\Models\Audio::factory()->create();
            
            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('@studio-add-button', 20)
                
                // 1. Open Dropdown
                ->click('@studio-add-button')
                ->waitFor('@studio-add-dropdown')
                ->assertVisible('@studio-add-dropdown')
                
                // 2. Click Outside (e.g., on the main layout header or body)
                // We'll click on the "Full View" button as a safe "outside" target
                ->click('#studio-full-view-btn')
                
                // 3. Verify Dropdown Closed
                ->waitUntilMissing('@studio-add-dropdown')
                ->assertMissing('@studio-add-dropdown');
        });
    }
}
