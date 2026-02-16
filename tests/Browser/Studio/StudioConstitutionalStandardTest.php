<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Audio;
use App\Models\Video;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Illuminate\Foundation\Testing\DatabaseTruncation;

class StudioConstitutionalStandardTest extends DuskTestCase
{
    use DatabaseTruncation;
    /**
     * @test
     */
    public function it_enforces_constitutional_marker_standards_across_all_entities()
    {
        $this->browse(function (Browser $browser) {
            $user = User::query()->where('email', 'test-editor@example.com')->first() 
                ?? User::factory()->create(['email' => 'test-editor@example.com']);

            $matrix = [
                'book' => [
                    'model' => Book::class,
                    'types' => ['chapter', 'page', 'section']
                ],
                'manuscript' => [
                    'model' => Manuscript::class,
                    'types' => ['folio', 'chapter']
                ],
                'audio' => [
                    'model' => Audio::class,
                    'types' => ['segment', 'track']
                ],
                'video' => [
                    'model' => Video::class,
                    'types' => ['segment', 'scene']
                ],
            ];

            foreach ($matrix as $entityKey => $config) {
                // 1. Create Entity
                $entity = $config['model']::factory()->create([
                    'title' => "Constitutional Test " . ucfirst($entityKey),
                    'slug' => "const-test-{$entityKey}-" . uniqid()
                ]);

                $browser->loginAs($user)
                    ->visitRoute('studio.show', ['type' => $entityKey, 'slug' => $entity->slug])
                    ->waitFor('#studio-save-btn');

                foreach ($config['types'] as $subType) {
                    $nodeTitle = "نواة " . $subType . " " . uniqid();
                    
                    // 2. Add via Toolbar
                    $browser->click('@studio-add-button')
                        ->waitFor('@studio-add-dropdown')
                        ->click("@type-option-{$subType}")
                        ->waitFor('@node-title-input')
                        ->type('@node-title-input', $nodeTitle);

                    // For audio/video, might need time input, but usually it defaults to 0
                    if (in_array($entityKey, ['audio', 'video'])) {
                        // Just ensure time is set if needed, but defaults are usually fine
                    }

                    $browser->click('@studio-add-submit')
                        ->waitUntilMissing('@studio-add-dropdown')
                        // Wait for Axios Save & Router Reload Cycle
                        ->waitUntil("document.querySelector('.tiptap')?.textContent.includes('$nodeTitle')", 15)
                        ->pause(1500);

                    // 3. CONSTITUTION_CHECK: Verify marker formatting
                    // According to .agent/the_atomic_encyclopedia_of_master.md:
                    // <h4 class="structure-marker" data-segment-link="true" ...>TITLE:</h4>
                    
                    $result = $browser->script("
                        const editor = window.editor;
                        if (!editor) return { status: 'NO_EDITOR' };
                        
                        const html = editor.getHTML();
                        const dom = document.querySelector('.tiptap')?.innerHTML || 'EMPTY';
                        
                        // Check for marker in parsed HTML
                        const hasMarker = html.includes('structure-marker');
                        const hasNodeTitle = html.includes('$nodeTitle');
                        
                        const marker = Array.from(document.querySelectorAll('.tiptap .structure-marker'))
                            .find(m => m.textContent.includes('$nodeTitle'));
                        
                        return {
                            status: (hasMarker && hasNodeTitle) ? 'FOUND' : 'NOT_FOUND',
                            htmlCount: (html.match(new RegExp('$nodeTitle', 'g')) || []).length,
                            markerDetected: hasMarker,
                            domContent: dom,
                            editorHtml: html,
                            foundNodeHtml: marker ? marker.outerHTML : null,
                            foundNodeTagName: marker ? marker.tagName : null,
                            editorJson: editor.getJSON()
                        };
                    ")[0];

                    if ($result['status'] === 'NOT_FOUND') {
                        \Illuminate\Support\Facades\Log::warning("\n[DUSK_DEBUG] Marker NOT_FOUND for: $nodeTitle in $entityKey");
                        \Illuminate\Support\Facades\Log::warning("[DUSK_DEBUG] Marker Detected in HTML: " . ($result['markerDetected'] ? 'YES' : 'NO'));
                        \Illuminate\Support\Facades\Log::warning("[DUSK_DEBUG] Editor HTML: " . $result['editorHtml']);
                        \Illuminate\Support\Facades\Log::warning("[DUSK_DEBUG] JSON State: " . json_encode($result['editorJson']));
                        
                        // Capture Browser Logs
                        $consoleLogs = $browser->driver->manage()->getLog('browser');
                        \Illuminate\Support\Facades\Log::warning("[DUSK_DEBUG] Browser Logs: " . json_encode($consoleLogs));

                         $browser->dump(); 
                    }

                    $this->assertEquals('FOUND', $result['status'], "Marker for $nodeTitle not found in $entityKey (Constitutional Standard not detected in Editor HTML)");
                    
                    $markerHtml = $result['foundNodeHtml'];
                    if (!$markerHtml) {
                         \Illuminate\Support\Facades\Log::warning("[DUSK_DEBUG] markerHtml is NULL. HTML: " . $result['editorHtml']);
                    }
                    // Standard Assertion: Must be a heading with structure-marker class
                    $this->assertNotEmpty($markerHtml, "Marker HTML should not be empty for $subType in $entityKey. HTML: {$result['editorHtml']}");
                    $this->assertMatchesRegularExpression('/^H[1-6]$/i', $result['foundNodeTagName'], "Marker for $subType in $entityKey should be a heading tag (H1-H6). Found: {$result['foundNodeTagName']}");
                    $this->assertStringContainsString('structure-marker', $markerHtml, "Marker for $subType in $entityKey should have 'structure-marker' class.");
                    
                    // Duplication Assertion: Must appear exactly ONCE in the raw HTML
                    $this->assertEquals(1, $result['htmlCount'], "Title '$nodeTitle' duplicated in $entityKey editor.");
                }
            }
        });
    }
}
