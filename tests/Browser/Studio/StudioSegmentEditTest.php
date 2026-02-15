<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Audio;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioSegmentEditTest extends DuskTestCase
{
    /**
     * @test
     */
    public function it_persists_segment_title_changes_from_player()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'Segment Edit Test Audio',
                'slug' => 'segment-edit-' . uniqid(),
                'duration' => 600
            ]);

            // Add an initial node
            $service = app(\App\Services\EntityContentService::class);
            $node = $service->addNode($audio, 'segment', 'Initial Title', 0);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('#studio-save-btn', 15)
                
                // Open player if not open
                ->script("if(!window.MediaStore.isOpen) window.MediaStore.setOpen(true)");
            
            $browser->waitFor('.pot-window-v2', 10)
                // Ensure Playlist is open
                ->script("if(!window.MediaStore.isPlaylistOpen) window.MediaStore.togglePlaylist()");
            
            $browser->waitFor('.playlist', 10)
                ->waitFor('.item', 10)
                ->assertSee('Initial Title')
                
                // Select the segment first (to make it active, enabling the edit button)
                ->click('.item')
                ->pause(1000)

                // Click Edit Icon in Playlist Header (Title is "Edit Segment Title")
                ->click('[title="Edit Segment Title"]')
                ->waitFor('.playlist input[placeholder="العنوان"]', 5)
                
                // Type new title
                ->type('.playlist input[placeholder="العنوان"]', 'Updated Segment Title')
                
                // Click Save (Checkmark) - Title is "حفظ"
                ->click('.playlist button[title="حفظ"]')
                
                // Pause to Allow Save and Reload
                ->pause(4000);

            // Verify in Database
            $updatedNode = $service->getNode($audio, $node->_id ?? $node->id);
            $this->assertEquals('Updated Segment Title', $updatedNode->title, "Segment title was not updated in database.");
        });
    }

    /**
     * @test
     */
    public function it_persists_segment_title_changes_from_editor()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'Editor Title Edit Test',
                'slug' => 'editor-edit-' . uniqid(),
                'duration' => 600
            ]);

            $service = app(\App\Services\EntityContentService::class);
            $node = $service->addNode($audio, 'segment', 'Original Editor Title', 0);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            // Set content in Tiptap directly for precise test
            // We mimic the exact marker format producing an updated title
            $newTitle = "Manually Edited Title";
            $startTime = 0;
            $headerHtml = "<p><strong><span data-segment-link=\"true\" data-id=\"{$node->id}\" data-start-time=\"{$startTime}\">{$newTitle}:</span></strong></p>";
            $contentHtml = "<p>New segment content</p>";
            
            $fullHtml = $headerHtml . $contentHtml;
            
            $browser->script("window.EditorStore.updateContent('{$fullHtml}')");
            $browser->pause(1000); // Wait for Tiptap to catch up
            
            $browser->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10);

            // Verify in Database
            $updatedNode = $service->getNode($audio, $node->_id ?? $node->id);
            $this->assertEquals($newTitle, $updatedNode->title, "Segment title edited in editor was not updated in database.");
        });
    }

    /**
     * @test
     */
    public function it_maps_segments_by_id_regardless_of_order_in_full_view()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'ID Mapping Stress Test',
                'slug' => 'id-mapping-' . uniqid(),
                'duration' => 600
            ]);

            $service = app(\App\Services\EntityContentService::class);
            // Create two segments
            $nodeA = $service->addNode($audio, 'segment', 'Segment A', 0);
            $nodeB = $service->addNode($audio, 'segment', 'Segment B', 10);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            // CHALLENGE: Inject HTML where B comes before A in the editor
            // And both have modified content/titles
            $newTitleA = "Updated Alpha";
            $newContentA = "<p>Content for Alpha</p>";
            $newTitleB = "Updated Beta";
            $newContentB = "<p>Content for Beta</p>";

            $htmlB = "<p><strong><span data-segment-link=\"true\" data-id=\"{$nodeB->id}\" data-start-time=\"10\">{$newTitleB}:</span></strong></p>" . $newContentB;
            $htmlA = "<p><strong><span data-segment-link=\"true\" data-id=\"{$nodeA->id}\" data-start-time=\"0\">{$newTitleA}:</span></strong></p>" . $newContentA;
            
            // Reversed order in HTML mass
            $fullHtml = $htmlB . "<p><br/></p>" . $htmlA;
            
            $browser->script("window.EditorStore.updateContent('{$fullHtml}')");
            $browser->pause(1000);
            
            $browser->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10);

            // Verify Segment A correctly mapped its content despite being second in HTML
            $updatedA = $service->getNode($audio, (string)$nodeA->id);
            $this->assertEquals($newTitleA, $updatedA->title, "Segment A title mapping failed in reversed order.");
            $this->assertStringContainsString("Content for Alpha", $updatedA->content, "Segment A content mapping failed in reversed order.");

            // Verify Segment B correctly mapped its content despite being first in HTML
            $updatedB = $service->getNode($audio, (string)$nodeB->id);
            $this->assertEquals($newTitleB, $updatedB->title, "Segment B title mapping failed in reversed order.");
            $this->assertStringContainsString("Content for Beta", $updatedB->content, "Segment B content mapping failed in reversed order.");
        });
    }

    /**
     * @test
     */
    public function it_persists_changes_after_reload_in_full_view()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'Reload Persistence Test',
                'slug' => 'reload-test-' . uniqid(),
                'duration' => 600
            ]);

            $service = app(\App\Services\EntityContentService::class);
            $node = $service->addNode($audio, 'segment', 'Initial Title', 0);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            $newTitle = "Title Checked After Reload";
            $newContent = "Content that should survive reload";
            $startTime = 0;
            $headerHtml = "<p><strong><span data-segment-link=\"true\" data-id=\"{$node->id}\" data-start-time=\"{$startTime}\">{$newTitle}:</span></strong></p>";
            $contentHtml = "<p>{$newContent}</p>";
            
            $browser->script("window.EditorStore.updateContent('{$headerHtml}{$contentHtml}')");
            $browser->pause(1000);
            
            $browser->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10);

            // RELOAD
            $browser->refresh()
                ->waitFor('.ProseMirror', 15)
                ->pause(2000); // Wait for potential async loads

            // Verify both in UI and DB
            $browser->assertSee($newTitle)
                ->assertSee($newContent);

            $updatedNode = $service->getNode($audio, (string)$node->id);
            $this->assertEquals($newTitle, $updatedNode->title, "Title did not persist after reload.");
            $this->assertStringContainsString($newContent, $updatedNode->content, "Content did not persist after reload.");
        });
    }

    /**
     * @test
     */
    public function it_persists_html_and_json_independently()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'Format Independence Test',
                'slug' => 'format-test-' . uniqid()
            ]);

            $service = app(\App\Services\EntityContentService::class);
            $node = $service->addNode($audio, 'segment', 'Initial', 0);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            $newText = "Specific text for JSON check " . uniqid();
            
            // Type into editor to generate JSON
            $browser->click('.ProseMirror')
                ->keys('.ProseMirror', ['{control}', 'a'], '{backspace}')
                ->type('.ProseMirror', $newText);
            
            $browser->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10);

            // Fetch from DB directly
            $updatedNode = $service->getNode($audio, (string)$node->id);
            
            $this->assertNotNull($updatedNode->content, "HTML content is null in DB.");
            $this->assertNotNull($updatedNode->json_content, "JSON content is null in DB.");
            $this->assertStringContainsString($newText, $updatedNode->content, "HTML does not contain new text.");
            
            // Verify JSON contains the text (it should be in a text node)
            $jsonString = json_encode($updatedNode->json_content);
            $this->assertStringContainsString($newText, $jsonString, "JSON does not contain new text.");
        });
    }

    /**
     * @test
     */
    public function it_persists_arabic_content_correctly()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $audio = Audio::factory()->create([
                'title' => 'Arabic Persistence Test',
                'slug' => 'arabic-test-' . uniqid()
            ]);

            $service = app(\App\Services\EntityContentService::class);
            $node = $service->addNode($audio, 'segment', 'عنوان قديم', 0);

            $browser->loginAs($user)
                ->visitRoute('studio.show', ['type' => 'audio', 'slug' => $audio->slug])
                ->waitFor('.ProseMirror', 15);
            
            $newTitle = "عنوان عربي جديد " . rand(100, 999);
            $newContent = "محتوى عربي محفوظ في قاعدة البيانات";
            
            // Reconstruct full HTML with Arabic
            $html = "<p><strong><span data-segment-link=\"true\" data-id=\"{$node->id}\" data-start-time=\"0\">{$newTitle}:</span></strong></p><p>{$newContent}</p>";
            
            $browser->script("window.EditorStore.updateContent('{$html}')");
            $browser->pause(1000);
            
            $browser->click('#studio-save-btn')
                ->waitForText('تم الحفظ بنجاح', 10);

            // RELOAD and check
            $browser->refresh()
                ->waitFor('.ProseMirror', 15)
                ->pause(1000)
                ->assertSee($newTitle)
                ->assertSee($newContent);

            $updatedNode = $service->getNode($audio, (string)$node->id);
            $this->assertEquals($newTitle, $updatedNode->title);
            $this->assertStringContainsString($newContent, $updatedNode->content);
        });
    }
}
