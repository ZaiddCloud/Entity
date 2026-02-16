<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use App\Models\AudioSegment;
use Illuminate\Support\Str;

class StudioReloadTest extends DuskTestCase
{
    use DatabaseTruncation;
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

            $nodeAId = (string) Str::uuid();
            $nodeA = new AudioSegment();
            $nodeA->_id = $nodeAId;
            $nodeA->audio_id = $audio->id;
            $nodeA->type = 'segment';
            $nodeA->title = 'المقطع الأول';
            $nodeA->order = 1;
            $nodeA->slug = 'segment-1-'.uniqid();
            $nodeA->start_time = 0;
            $nodeA->save();

            $nodeBId = (string) Str::uuid();
            $nodeB = new AudioSegment();
            $nodeB->_id = $nodeBId;
            $nodeB->audio_id = $audio->id;
            $nodeB->type = 'segment';
            $nodeB->title = 'المقطع الثاني';
            $nodeB->order = 2;
            $nodeB->slug = 'segment-2-'.uniqid();
            $nodeB->start_time = 10;
            $nodeB->save();

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            $newTitleA = "عنوان أ " . rand(100, 999);
            $newContentA = "محتوى مقطع أ المعدل";
            $newTitleB = "عنوان ب " . rand(100, 999);
            $newContentB = "محتوى مقطع ب المعدل";
            
            // REORDER B before A in the editor
            $markerB = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$nodeBId}\" data-type=\"segment\" data-start-time=\"10\">{$newTitleB}</h4>";
            $contentB = "<p>{$newContentB}</p>";
            $markerA = "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$nodeAId}\" data-type=\"segment\" data-start-time=\"0\">{$newTitleA}</h4>";
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
            $service = app(\App\Services\EntityContentService::class);
            $freshA = $service->getNode($audio, $nodeAId);
            $freshB = $service->getNode($audio, $nodeBId);
            
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
