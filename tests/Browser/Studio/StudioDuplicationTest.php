<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioDuplicationTest extends DuskTestCase
{
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

            // Add one initial node via service to have something to save
            $service = app(\App\Services\EntityContentService::class);
            $node = $service->addNode($audio, 'segment', 'المقطع الأول', 0);

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

            // Assert that the title "المقطع الأول:" only appears once in the editor mass
            // We use a script to check the occurrence count
            $count = $browser->script("
                return (document.querySelector('.tiptap').innerHTML.match(/المقطع الأول/g) || []).length;
            ")[0];

            $this->assertEquals(1, $count, "Header 'المقطع الأول' was duplicated after multiple saves.");
        });
    }
}
