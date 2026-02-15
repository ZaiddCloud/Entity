<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioPlayerEditTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_persists_title_changes_from_player_and_syncs_to_editor()
    {
        $audio = Audio::factory()->create([
            'title' => 'Player Sync Test',
            'slug' => 'player-sync-' . uniqid()
        ]);

        $service = app(\App\Services\EntityContentService::class);
        $node = $service->addNode($audio, 'segment', 'Original Title', 0);

        $this->browse(function (Browser $browser) use ($service, $audio, $node) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            try {
                $browser->loginAs($user)
                    ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                    ->waitFor('.ProseMirror', 15);
                
                // 1. Open Player Playlist
                $browser->script("window.MediaStore.setOpen(true)");
                $browser->script("window.MediaStore.isPlaylistOpen = true");
                $browser->script("window.MediaStore.activeSegmentSlug = '{$node->slug}'");
                $browser->waitFor('.playlist', 10);
                
                // 2. Edit title in Player
                $newTitle = "Title from Player " . rand(100, 999);
                
                // Find the edit button for the segment
                $browser->script("document.querySelector('[title=\"Edit Segment Title\"]').click()");
                
                $browser->waitFor('input[placeholder="العنوان"]', 10)
                    ->keys('input[placeholder="العنوان"]', ['{control}', 'a'], '{backspace}')
                    ->type('input[placeholder="العنوان"]', $newTitle);
                
                // Click save button
                $browser->click('button[title="حفظ"]');

                // Handle any alerts that appear (check multiple times)
                for ($i = 0; $i < 5; $i++) {
                    try {
                        $alert = $browser->driver->switchTo()->alert();
                        $alertText = $alert->getText();
                        echo "\n[StudioPlayerEditTest] Alert detected: {$alertText}\n";
                        $alert->accept();
                        echo "[StudioPlayerEditTest] Alert accepted\n";
                        break;
                    } catch (\Exception $e) {
                        // No alert yet, wait a bit
                        usleep(200000); // 200ms
                    }
                }

                // 3. Verify it updated in the Editor (Wait for Inertia reload + Sync)
                $browser->pause(3000);
                $textResult = $browser->script("return document.querySelector('.ProseMirror')?.innerText || 'NOT FOUND'");
                $text = is_array($textResult) ? $textResult[0] : $textResult;
                echo "\n[StudioPlayerEditTest] Editor InnerText: " . $text . "\n";
                
                $browser->waitForTextIn('.ProseMirror', $newTitle, 15);
                
                // 4. Save in Editor
                $browser->click('#studio-save-btn')
                    ->waitForTextIn('#studio-save-status', 'تم الحفظ بنجاح', 10);
                
                // 5. Final verification in DB
                $updatedNode = $service->getNode($audio, (string)$node->id);
                $this->assertEquals($newTitle, $updatedNode->title, "Player title was overwritten by stale editor content.");

            } finally {
                $logs = $browser->driver->manage()->getLog('browser');
                echo "\n[StudioPlayerEditTest] Console Logs:\n";
                print_r($logs);
            }
        });
    }
}
