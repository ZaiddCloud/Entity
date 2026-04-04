<?php

namespace Tests\Feature;

use App\Models\Manuscript;
use App\Models\Audio;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EntityVersioningTest extends TestCase
{
    // use RefreshDatabase; // Skipping RefreshDatabase to preserve seeded data for manual inspection if needed, or use separate test DB. 
    // Ideally use RefreshDatabase in CI/CD. For now, we will just create and delete.

    public function test_can_store_manuscript_with_detailed_metadata()
    {
        $data = [
            'title' => 'Sahih Bukhari - Test Copy',
            'slug' => 'sahih-bukhari-test-' . uniqid(),
            'original_title' => 'Al-Jami Al-Sahih',
            'code' => 'BUKHARI_TEST_001',
            'scribe' => 'Ibn Al-Waraq',
            'script_type' => 'Naskh',
            'inscriptions' => 'Owned by Sultan Qaitbay',
        ];

        $manuscript = Manuscript::create($data);

        $this->assertDatabaseHas('manuscripts', [
            'id' => $manuscript->id,
            'scribe' => 'Ibn Al-Waraq',
            'code' => 'BUKHARI_TEST_001',
        ]);

        $manuscript->delete();
    }

    public function test_can_group_entities_by_code()
    {
        $code = 'GROUP_' . uniqid();

        // Create 2 manuscripts with same code
        $m1 = Manuscript::create(['title' => 'Copy A', 'slug' => 'copy-a-' . uniqid(), 'code' => $code]);
        $m2 = Manuscript::create(['title' => 'Copy B', 'slug' => 'copy-b-' . uniqid(), 'code' => $code]);
        $m3 = Manuscript::create(['title' => 'Copy C', 'slug' => 'copy-c-' . uniqid(), 'code' => 'OTHER_CODE']);

        // Fetch cousins using the logic we plan to use in Controller
        $siblingsValid = Manuscript::where('code', $code)->get();
        
        $this->assertEquals(2, $siblingsValid->count());
        $this->assertTrue($siblingsValid->contains($m1));
        $this->assertTrue($siblingsValid->contains($m2));
        $this->assertFalse($siblingsValid->contains($m3));

        $m1->delete();
        $m2->delete();
        $m3->delete();
    }

    public function test_audio_versioning()
    {
        $code = 'AUDIO_GRP_' . uniqid();
        $a1 = Audio::create(['title' => 'Recitation A', 'slug' => 'rec-a-' . uniqid(), 'code' => $code]);
        $a2 = Audio::create(['title' => 'Recitation B', 'slug' => 'rec-b-' . uniqid(), 'code' => $code]);

        $siblings = Audio::where('code', $code)->get();
        $this->assertEquals(2, $siblings->count());
    }

    public function test_video_versioning()
    {
        $code = 'VID_GRP_' . uniqid();
        $v1 = Video::create(['title' => 'Lesson 1 HD', 'slug' => 'vid-1-' . uniqid(), 'code' => $code]);
        $v2 = Video::create(['title' => 'Lesson 1 SD', 'slug' => 'vid-2-' . uniqid(), 'code' => $code]);

        $siblings = Video::where('code', $code)->get();
        $this->assertEquals(2, $siblings->count());
    }

    public function test_controller_passes_siblings_to_view()
    {
        // 1. Create Data
        $code = 'CONTROLLER_TEST_' . uniqid();
        $mMain = Manuscript::create(['title' => 'Main Copy', 'slug' => 'main-' . uniqid(), 'code' => $code]);
        $mSibling = Manuscript::create(['title' => 'Sibling Copy', 'slug' => 'sibling-' . uniqid(), 'code' => $code]);

        // 2. act as user
        $user = \App\Models\User::factory()->create();
        
        // 3. Make Request to Show Page
        $response = $this->actingAs($user)->get(route('manuscripts.show', $mMain->slug));

        // 4. Assert response contains 'siblings' prop with the sibling
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Manuscripts/Show')
            ->has('siblings', 1)
            ->where('siblings.0.id', $mSibling->id)
            ->where('siblings.0.title', 'Sibling Copy')
        );
    }
}
