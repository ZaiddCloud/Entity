<?php

namespace Tests\Feature;

use App\Models\Audio;
use App\Models\Manuscript;
use App\Models\Video;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaControllersTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        Storage::fake('public');
    }

    // ==========================================
    // AUDIO TESTS
    // ==========================================

    /** @test */
    public function can_create_audio()
    {
        $file = UploadedFile::fake()->create('track.mp3', 100);
        $data = [
            'title' => 'New Audio',
            'file' => $file,
            'description' => 'Test Description',
        ];

        $response = $this->post(route('audios.store'), $data);

        $response->assertRedirect(route('audios.index'));
        $this->assertDatabaseHas('audios', ['title' => 'New Audio']);
        Storage::disk('public')->assertExists("audio/" . $file->hashName());
    }

    /** @test */
    public function can_update_audio()
    {
        $audio = Audio::factory()->create();
        $data = ['title' => 'Updated Audio Title'];

        $response = $this->put(route('audios.update', $audio), $data);

        $audio->refresh();
        $response->assertRedirect(route('audios.show', $audio));
        $this->assertDatabaseHas('audios', ['id' => $audio->id, 'title' => 'Updated Audio Title']);
    }

    /** @test */
    public function can_delete_audio()
    {
        $audio = Audio::factory()->create();
        $response = $this->delete(route('audios.destroy', $audio));
        
        $response->assertRedirect(route('audios.index'));
        $this->assertSoftDeleted('audios', ['id' => $audio->id]);
    }

    // ==========================================
    // VIDEO TESTS
    // ==========================================

    /** @test */
    public function can_create_video()
    {
        $file = UploadedFile::fake()->create('movie.mp4', 100);
        $data = [
            'title' => 'New Video',
            'file' => $file,
            'description' => 'Test Video',
        ];

        $response = $this->post(route('videos.store'), $data);

        $response->assertRedirect(route('videos.index'));
        $this->assertDatabaseHas('videos', ['title' => 'New Video']);
        Storage::disk('public')->assertExists("videos/" . $file->hashName());
    }

    /** @test */
    public function can_update_video()
    {
        $video = Video::factory()->create();
        $data = ['title' => 'Updated Video Title'];

        $response = $this->put(route('videos.update', $video), $data);

        $video->refresh();
        $response->assertRedirect(route('videos.show', $video));
        $this->assertDatabaseHas('videos', ['id' => $video->id, 'title' => 'Updated Video Title']);
    }

    /** @test */
    public function can_delete_video()
    {
        $video = Video::factory()->create();
        $response = $this->delete(route('videos.destroy', $video));
        
        $response->assertRedirect(route('videos.index'));
        $this->assertSoftDeleted('videos', ['id' => $video->id]);
    }

    // ==========================================
    // MANUSCRIPT TESTS
    // ==========================================

    /** @test */
    public function can_create_manuscript()
    {
        $file = UploadedFile::fake()->create('manuscript.pdf', 100);
        $data = [
            'title' => 'New Manuscript',
            'file' => $file,
            'description' => 'Old Text',
        ];

        $response = $this->post(route('manuscripts.store'), $data);

        $response->assertRedirect(route('manuscripts.index'));
        $this->assertDatabaseHas('manuscripts', ['title' => 'New Manuscript']);
        Storage::disk('public')->assertExists("manuscripts/" . $file->hashName());
    }

    /** @test */
    public function can_update_manuscript()
    {
        $manuscript = Manuscript::factory()->create();
        $data = ['title' => 'Updated Manuscript Title'];

        $response = $this->put(route('manuscripts.update', $manuscript), $data);

        $manuscript->refresh();
        $response->assertRedirect(route('manuscripts.show', $manuscript));
        $this->assertDatabaseHas('manuscripts', ['id' => $manuscript->id, 'title' => 'Updated Manuscript Title']);
    }

    /** @test */
    public function can_delete_manuscript()
    {
        $manuscript = Manuscript::factory()->create();
        $response = $this->delete(route('manuscripts.destroy', $manuscript));
        
        $response->assertRedirect(route('manuscripts.index'));
        $this->assertSoftDeleted('manuscripts', ['id' => $manuscript->id]);
    }
}
