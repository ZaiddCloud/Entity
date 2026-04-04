<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Integration Test: Full Manuscript Creation Flow
 * Tests the complete lifecycle from HTTP Request → Validation → Service → Database → Response
 */
class ManuscriptCreationIntegrationTest extends TestCase
{
    public function test_complete_manuscript_creation_with_all_new_fields()
    {
        $user = User::factory()->create();
        
        $manuscriptData = [
            'title' => 'Test Manuscript - Complete Flow',
            'original_title' => 'الجامع الصحيح المسند',
            'code' => 'INTEGRATION_TEST_' . uniqid(),
            'catalog_number' => 'MS-1234-AB',
            'scribe' => 'محمد بن أحمد الكاتب',
            'copy_date' => '850 هـ',
            'parts' => '3',
            'script_type' => 'نسخ',
            'dimensions' => '25x18 سم',
            'lines_per_page' => 25,
            'inscriptions' => 'تملك: السلطان قايتباي',
            'notes' => 'نسخة نفيسة بخط جميل',
            'manuscript_century' => '9',
            'manuscript_century_label' => '9 هـ',
            'description' => 'نسخة نادرة من المخطوط',
            'author_ids' => [],
            'categories' => [],
            'tags' => [],
        ];

        // Simulate HTTP POST Request
        $response = $this->actingAs($user)->post(route('manuscripts.store'), $manuscriptData);

        // Debug: Check for validation errors
        if ($response->status() === 302 && $response->headers->get('Location') === 'http://127.0.0.1:8000') {
            $session = $response->getSession();
            if ($session && $session->has('errors')) {
                $errors = $session->get('errors');
                dump('Validation failed for fields:', $errors->keys());
                foreach ($errors->keys() as $key) {
                    dump("Field: $key", $errors->get($key));
                }
            }
        }

        // Assert redirect success
        $response->assertRedirect(route('manuscripts.index'));
        $response->assertSessionHas('message');

        // Verify Database Storage
        $this->assertDatabaseHas('manuscripts', [
            'title' => 'Test Manuscript - Complete Flow',
            'original_title' => 'الجامع الصحيح المسند',
            'code' => $manuscriptData['code'],
            'catalog_number' => 'MS-1234-AB',
            'scribe' => 'محمد بن أحمد الكاتب',
            'copy_date' => '850 هـ',
            'inscriptions' => 'تملك: السلطان قايتباي',
        ]);

        // Retrieve and Verify
        $manuscript = Manuscript::where('code', $manuscriptData['code'])->first();
        $this->assertNotNull($manuscript);
        $this->assertEquals('نسخ', $manuscript->script_type);
        $this->assertEquals(25, $manuscript->lines_per_page);
    }

    public function test_siblings_are_returned_in_show_response()
    {
        $user = User::factory()->create();
        $code = 'SIBLING_TEST_' . uniqid();

        // Create Main Manuscript
        $main = Manuscript::create([
            'title' => 'Main Copy',
            'slug' => 'main-' . uniqid(),
            'code' => $code,
            'scribe' => 'الناسخ الأول',
        ]);

        // Create Sibling
        $sibling = Manuscript::create([
            'title' => 'Sibling Copy',
            'slug' => 'sibling-' . uniqid(),
            'code' => $code,
            'scribe' => 'الناسخ الثاني',
        ]);

        // Request Show Page
        $response = $this->actingAs($user)->get(route('manuscripts.show', $main->slug));

        // Verify Response Structure
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Manuscripts/Show')
            ->has('manuscript')
            ->has('siblings', 1)
            ->where('siblings.0.title', 'Sibling Copy')
        );
    }

    public function test_audio_and_video_code_field_works()
    {
        $user = User::factory()->create();
        
        // Test Audio
        $audioData = [
            'title' => 'Test Audio Recording',
            'code' => 'AUDIO_TEST_' . uniqid(),
            'description' => 'Test description',
        ];

        $audioResponse = $this->actingAs($user)->post(route('audios.store'), $audioData);
        $audioResponse->assertRedirect(route('audios.index'));
        
        $this->assertDatabaseHas('audios', [
            'title' => 'Test Audio Recording',
            'code' => $audioData['code'],
        ]);

        // Test Video
        $videoData = [
            'title' => 'Test Video Recording',
            'code' => 'VIDEO_TEST_' . uniqid(),
            'description' => 'Test description',
        ];

        $videoResponse = $this->actingAs($user)->post(route('videos.store'), $videoData);
        $videoResponse->assertRedirect(route('videos.index'));
        
        $this->assertDatabaseHas('videos', [
            'title' => 'Test Video Recording',
            'code' => $videoData['code'],
        ]);
    }
}
