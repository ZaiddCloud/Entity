<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EntityWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_video_with_files()
    {
        Storage::fake('public');
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $cover = UploadedFile::fake()->image('video_cover.jpg');
        $videoFile = UploadedFile::fake()->create('movie.mp4', 5000, 'video/mp4');

        $response = $this->actingAs($user)->post(route('videos.store'), [
            'type' => 'video',
            'title' => 'My Awesome Video',
            'description' => 'A test video description.',
            'cover' => $cover,
            'file' => $videoFile,
        ]);

        $response->assertRedirect(route('videos.index'));

        $this->assertDatabaseHas('videos', [
            'title' => 'My Awesome Video',
            'format' => 'mp4',
        ]);

        $video = Video::where('title', 'My Awesome Video')->first();
        $this->assertNotNull($video->cover_path);
        $this->assertNotNull($video->file_path);

        Storage::disk('public')->assertExists($video->cover_path);
        Storage::disk('public')->assertExists($video->file_path);
    }

    public function test_authenticated_user_can_update_video_with_files()
    {
        Storage::fake('public');
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        $video = Video::create(['title' => 'Old Video', 'slug' => 'old-video', 'type' => 'video']);

        $newCover = UploadedFile::fake()->image('new_cover.jpg');
        
        $response = $this->actingAs($user)->post(route('videos.update', $video->id), [
            '_method' => 'PUT',
            'title' => 'Updated Video',
            'cover' => $newCover,
        ]);

        $response->assertRedirect(route('videos.show', $video->id));

        $video->refresh();
        $this->assertEquals('Updated Video', $video->title);
        $this->assertNotNull($video->cover_path);
        Storage::disk('public')->assertExists($video->cover_path);
    }

    public function test_authenticated_user_can_create_audio_with_files()
    {
        Storage::fake('public');
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $audioFile = UploadedFile::fake()->create('song.mp3', 3000, 'audio/mpeg');

        $response = $this->actingAs($user)->post(route('audios.store'), [
            'type' => 'audio',
            'title' => 'My Podcast',
            'description' => 'A nice podcast.',
            'file' => $audioFile,
        ]);

        $response->assertRedirect(route('audios.index'));

        $audio = Audio::where('title', 'My Podcast')->first();
        $this->assertNotNull($audio->file_path);
        Storage::disk('public')->assertExists($audio->file_path);
    }

    public function test_authenticated_user_can_create_manuscript_with_files()
    {
        Storage::fake('public');
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $pdf = UploadedFile::fake()->create('ancient.pdf', 2000, 'application/pdf');

        $response = $this->actingAs($user)->post(route('manuscripts.store'), [
            'type' => 'manuscript',
            'title' => 'Ancient Text',
            'description' => 'Very old text.',
            'file' => $pdf,
        ]);

        $response->assertRedirect(route('manuscripts.index'));

        $manuscript = Manuscript::where('title', 'Ancient Text')->first();
        $this->assertNotNull($manuscript->file_path);
        Storage::disk('public')->assertExists($manuscript->file_path);
    }
}
