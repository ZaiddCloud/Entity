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
}
