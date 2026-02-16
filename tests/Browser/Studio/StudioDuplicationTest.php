<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Support\Str;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use App\Models\AudioSegment;

class StudioDuplicationTest extends DuskTestCase
{
    use DatabaseTruncation;
    /**
     * @test
     */
    public function it_does_not_duplicate_content_on_multiple_saves()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);
            
            // Create target audio
            $audio = Audio::factory()->create([
                'title' => 'Duplication Test Audio',
                'slug' => 'duplication-test-' . uniqid(),
                'duration' => 600
            ]);

            // Add one initial node via manual creation for deterministic ID
            $nodeId = (string) Str::uuid();
            $node = new AudioSegment();
            $node->_id = $nodeId;
            $node->audio_id = $audio->id;
            $node->type = 'segment';
            $node->title = 'المقطع الأول';
            $node->order = 1;
            $node->slug = 'segment-1-'.uniqid();
            $node->start_time = 0;
            $node->content = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$nodeId}\" data-type=\"segment\" data-start-time=\"0\">المقطع الأول</h4><p>محتوى افتراضي</p>";
            $node->save();

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('#studio-save-btn')
                // We should be in Full View by default if no childId
                ->assertSee('كامل المحتوى')
                ->assertSee('المقطع الأول')
                
                // SAVE 1
                ->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10)
                ->pause(1000)
                
                // SAVE 2
                ->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10)
                ->pause(1000)
                
                // SAVE 3
                ->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10)
                ->pause(1000)
                
                // Refresh to check persisted state
                ->refresh()
                ->waitFor('#studio-save-btn')
                ->pause(2000);

            // Assert that the title "المقطع الأول" only appears once in the editor mass
            // We use a script to check the occurrence count
            // IMPORTANT: Wait for any background syncs
            $browser->pause(2000);
            
            $count = $browser->script("
                return document.querySelectorAll('.tiptap .structure-marker[data-id=\"{$nodeId}\"]').length;
            ")[0];

            $this->assertEquals(1, $count, "Header 'المقطع الأول' (ID: $nodeId) was duplicated or missing after multiple saves. Found: $count instances.");
        });
    }
}
