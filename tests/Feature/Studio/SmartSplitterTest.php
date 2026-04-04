<?php

namespace Tests\Feature\Studio;

use App\Models\User;
use App\Models\Audio;
use App\Models\AudioSegment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartSplitterTest extends TestCase
{
    /**
     * Test that Full View correctly aggregates all segments with markers.
     */
    public function test_full_view_aggregates_segments_with_markers()
    {
        $user = User::factory()->create();
        $audio = Audio::firstOrCreate(
            ['slug' => 'شرح-ألفية-ابن-مالك'],
            ['title' => 'شرح ألفية ابن مالك', 'duration' => 3600]
        );
        
        // Ensure we have multiple segments
        if ($audio->children->count() < 2) {
            AudioSegment::create([
                'audio_id' => $audio->id,
                'slug' => 'test-segment-2',
                'title' => 'المقطع الثاني',
                'order' => 2,
                'start_time' => 300,
                'content' => '<p>محتوى المقطع الثاني للاختبار.</p>'
            ]);
            $audio->refresh();
        }
        
        $segments = $audio->children()->orderBy('order')->get();
        
        // Make authenticated request to Full View (no childId = Full View)
        $response = $this->actingAs($user)
            ->get("/studio/audio/{$audio->slug}");
        
        $response->dump(); $response->assertStatus(200);
        
        // Verify Inertia props contain aggregated content
        $props = $response->viewData('page')['props'];
        
        $this->assertArrayHasKey('editorContent', $props);
        $aggregatedContent = $props['editorContent'];
        
        // Verify each segment appears with its marker
        foreach ($segments as $segment) {
            $this->assertStringContainsString(
                "<h4 class=\"structure-marker\"",
                $aggregatedContent,
                "Segment '{$segment->title}' marker not found in aggregated content"
            );
            $this->assertStringContainsString(
                $segment->title,
                $aggregatedContent,
                "Segment title not found"
            );
            
            // Verify segment content is present
            $cleanSegmentContent = strip_tags($segment->content);
            $cleanAggregatedContent = strip_tags($aggregatedContent);
            
            $this->assertStringContainsString(
                $cleanSegmentContent,
                $cleanAggregatedContent,
                "Segment '{$segment->title}' content not found in aggregated view"
            );
        }
    }
    
    /**
     * Test that saving in Full View correctly fragments content back to segments.
     */
    public function test_full_view_save_fragments_to_segments()
    {
        $user = User::factory()->create();
        $audio = Audio::firstOrCreate(
            ['slug' => 'شرح-ألفية-ابن-مالك'],
            ['title' => 'شرح ألفية ابن مالك', 'duration' => 3600]
        );
        
        // Ensure we have exactly 2 segments for predictable testing
        AudioSegment::where('audio_id', $audio->id)->delete();
        
        $segment1 = AudioSegment::create([
            'audio_id' => $audio->id,
            'slug' => 'segment-1',
            'title' => 'المقطع الأول',
            'order' => 1,
            'start_time' => 0,
            'content' => '<p>محتوى قديم 1</p>'
        ]);
        
        $segment2 = AudioSegment::create([
            'audio_id' => $audio->id,
            'slug' => 'segment-2',
            'title' => 'المقطع الثاني',
            'order' => 2,
            'start_time' => 300,
            'content' => '<p>محتوى قديم 2</p>'
        ]);
        
        // Simulate Full View save with new content
        // Format matches what backend expects: markers in <h4 class="structure-marker"> tags
        $newFullContent = 
            "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$segment1->id}\">{$segment1->title}</h4>\n" .
            "<p>محتوى جديد للمقطع الأول بعد التعديل</p>\n" .
            "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$segment2->id}\">{$segment2->title}</h4>\n" .
            "<p>محتوى جديد للمقطع الثاني بعد التعديل</p>";
        
        // Make POST request to save endpoint with child_id='full'
        $response = $this->actingAs($user)
            ->post("/studio/audio/{$audio->slug}/full/save", [
                'content' => $newFullContent,
                'child_id' => 'full'
            ]);
        
        $response->assertStatus(200);
        
        // Verify segments were updated correctly
        $newSegment1 = AudioSegment::where('title', 'المقطع الأول')->first();
        $newSegment2 = AudioSegment::where('title', 'المقطع الثاني')->first();
        
        $this->assertNotNull($newSegment1);
        $this->assertNotNull($newSegment2);
        
        $this->assertStringContainsString(
            'محتوى جديد للمقطع الأول',
            $newSegment1->content,
            'Segment 1 content was not updated correctly'
        );
        
        $this->assertStringContainsString(
            'محتوى جديد للمقطع الثاني',
            $newSegment2->content,
            'Segment 2 content was not updated correctly'
        );
        
        // Verify markers were removed from individual segments
        $this->assertStringNotContainsString(
            '<h4',
            $newSegment1->content,
            'Segment 1 should not contain marker tags'
        );
        
        $this->assertStringNotContainsString(
            '<h4',
            $newSegment2->content,
            'Segment 2 should not contain marker tags'
        );
    }
    
    /**
     * Test that SegmentLink markers are preserved during fragmentation.
     */
    public function test_segment_links_preserved_during_split()
    {
        $user = User::factory()->create();
        $audio = Audio::firstOrCreate(
            ['slug' => 'شرح-ألفية-ابن-مالك'],
            ['title' => 'شرح ألفية ابن مالك', 'duration' => 3600]
        );
        
        // Create segments
        AudioSegment::where('audio_id', $audio->id)->delete();
        
        $segment1 = AudioSegment::create([
            'audio_id' => $audio->id,
            'slug' => 'segment-1',
            'title' => 'المقطع الأول',
            'order' => 1,
            'start_time' => 0,
            'content' => '<p>محتوى عادي</p>'
        ]);
        
        // Full content with SegmentLink marker
        $newFullContent = 
            "<h4 class=\"structure-marker\" data-segment-link=\"true\" data-id=\"{$segment1->id}\">{$segment1->title}</h4>\n" .
            '<p>نص يحتوي على <span data-segment-link data-id="' . $segment1->_id . '" data-start-time="0" class="segment-link">رابط مقطع</span> داخله.</p>';
        
        $response = $this->actingAs($user)
            ->post("/studio/audio/{$audio->slug}/full/save", [
                'content' => $newFullContent,
                'child_id' => 'full'
            ]);
        
        $response->dump(); $response->assertStatus(200);
        
        $newSegment1 = AudioSegment::where('title', 'المقطع الأول')->first();
        $this->assertNotNull($newSegment1);
        
        // Verify SegmentLink attributes are preserved
        $this->assertStringContainsString('data-segment-link', $newSegment1->content);
        $this->assertStringContainsString('data-id', $newSegment1->content);
        $this->assertStringContainsString('data-start-time', $newSegment1->content);
        $this->assertStringContainsString('segment-link', $newSegment1->content);
    }
}
