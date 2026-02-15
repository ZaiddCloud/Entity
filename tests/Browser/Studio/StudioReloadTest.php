<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioReloadTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_maintains_changes_after_full_page_reload()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'Persistence Stress Test',
                'slug' => 'p-stress-' . uniqid()
            ]);

            $service = app(\App\Services\EntityContentService::class);
            $nodeA = $service->addNode($audio, 'segment', 'المقطع الأول', 0);
            $nodeB = $service->addNode($audio, 'segment', 'المقطع الثاني', 10);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            $newTitleA = "عنوان أ " . rand(100, 999);
            $newContentA = "محتوى مقطع أ المعدل";
            $newTitleB = "عنوان ب " . rand(100, 999);
            $newContentB = "محتوى مقطع ب المعدل";
            
            // REORDER B before A in the editor
            $markerB = "<p><strong><span data-segment-link=\"true\" data-id=\"{$nodeB->id}\" data-start-time=\"10\">{$newTitleB}:</span></strong></p>";
            $contentB = "<p>{$newContentB}</p>";
            $markerA = "<p><strong><span data-segment-link=\"true\" data-id=\"{$nodeA->id}\" data-start-time=\"0\">{$newTitleA}:</span></strong></p>";
            $contentA = "<p>{$newContentA}</p>";
            
            $fullHtml = $markerB . $contentB . $markerA . $contentA;
            
            $browser->script("window.EditorStore.updateContent('{$fullHtml}')");
            $browser->pause(1000);
            
            // Save
            $browser->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10);

            // Hard Reload
            $browser->refresh()
                ->waitFor('.ProseMirror', 15)
                ->pause(2000);

            // Assert Editor State (B should be first, A second)
            $browser->assertSee($newTitleB)
                ->assertSee($newContentB)
                ->assertSee($newTitleA)
                ->assertSee($newContentA);
            
            // Assert Database State
            $freshA = $service->getNode($audio, (string)$nodeA->id);
            $freshB = $service->getNode($audio, (string)$nodeB->id);
            
            $this->assertEquals($newTitleA, $freshA->title, "Segment A title mismatch.");
            $this->assertEquals($newTitleB, $freshB->title, "Segment B title mismatch.");
            $this->assertStringContainsString($newContentA, $freshA->content, "Segment A content mismatch.");
            $this->assertStringContainsString($newContentB, $freshB->content, "Segment B content mismatch.");
            
            // Print for log
            echo "\n[StudioReloadTest] Verified A Title: " . $freshA->title . "\n";
            echo "[StudioReloadTest] Verified B Title: " . $freshB->title . "\n";
            
            $logs = $browser->driver->manage()->getLog('browser');
            echo "[StudioReloadTest] Console Logs:\n";
            print_r($logs);
        });
    }
}
