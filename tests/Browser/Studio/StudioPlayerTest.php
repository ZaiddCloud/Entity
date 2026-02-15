<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class StudioPlayerTest extends DuskTestCase
{
    use DatabaseTruncation;

    /** @test */
    public function it_can_close_and_reopen_player()
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create([
            'slug' => 'audio-player-test',
            'title' => 'Audio Player Test',
            'file_path' => 'audio/sample-1.mp3'
        ]);

        $this->browse(function (Browser $browser) use ($user, $audio) {
            $browser->loginAs($user)
                ->visit(route('studio.show', ['type' => 'audio', 'slug' => $audio->slug]))
                ->waitFor('.pot-window-v2')
                ->assertVisible('.pot-window-v2')
                
                // Start Playback
                ->waitFor('.stage')
                ->pause(2000) // Give extra time for media load
                ->waitUntil('document.querySelector("audio") != null');
                
                $browser->click('button[title="Play/Pause"]')
                        ->pause(2000) // Wait for playback
                        ->assertScript('document.querySelector("audio").paused', false);

                // Close Player
                $browser->click('.win-btn.close')
                        ->waitUntilMissing('.pot-window-v2')
                ->assertMissing('.pot-window-v2')
                
                // Assert Audio Paused
                ->assertScript('document.querySelector("audio").paused', true)
                
                // Re-open Player
                ->waitFor('#studio-open-player-btn')
                ->click('#studio-open-player-btn')
                ->waitFor('.pot-window-v2')
                ->assertVisible('.pot-window-v2')
                
                // Button should be hidden again (assuming logic !mediaStore.isOpen)
                ->waitUntilMissing('#studio-open-player-btn')
                ->assertMissing('#studio-open-player-btn');
        });
    }
}
