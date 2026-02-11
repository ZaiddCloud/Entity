<?php

namespace Tests\Browser\Studio;

use App\Models\User;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;
use App\Enums\EntityType;
use App\Enums\ContentNodeType;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class StudioAccessTest extends DuskTestCase
{
    use DatabaseTruncation;

    /**
     * Test Audio Studio Access
     */
    public function test_can_access_audio_studio(): void
    {
        $user = User::factory()->create();
        $audio = Audio::factory()->create(['title' => 'Test Audio']);
        
        // Create a segment for the audio
        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Audio Segment 1',
            'slug' => 'audio-segment-1',
            'content' => '<p>Audio Content</p>',
            'order' => 1,
            'type' => ContentNodeType::SEGMENT->value
        ]);

        $this->browse(function (Browser $browser) use ($user, $audio) {
            $browser->visit('/')
                ->loginAs($user)
                ->visit("/studio/audio/{$audio->slug}")
                ->waitFor('header h1', 30)
                ->assertSeeIn('header h1', $audio->title)
                ->assertSee('Entity Studio');
        });
    }

    /**
     * Test Video Studio Access
     */
    public function test_can_access_video_studio(): void
    {
        $user = User::factory()->create();
        $video = Video::factory()->create(['title' => 'Test Video']);

        // Create a segment for the video
        VideoSegment::create([
            'video_id' => $video->id,
            'title' => 'Video Scene 1',
            'slug' => 'video-scene-1',
            'content' => '<p>Video Content</p>',
            'order' => 1,
            'type' => ContentNodeType::SCENE->value
        ]);

        $this->browse(function (Browser $browser) use ($user, $video) {
            $browser->visit('/')
                ->loginAs($user)
                ->visit("/studio/video/{$video->slug}")
                ->waitFor('header h1', 30)
                ->assertSeeIn('header h1', $video->title)
                ->assertSee('Entity Studio');
        });
    }

    /**
     * Test Manuscript Studio Access
     */
    public function test_can_access_manuscript_studio(): void
    {
        $user = User::factory()->create();
        $manuscript = Manuscript::factory()->create(['title' => 'Test Manuscript']);

        // Create a page for the manuscript
        ManuscriptPage::create([
            'manuscript_id' => $manuscript->id,
            'title' => 'Page 1',
            'slug' => 'page-1',
            'content' => '<p>Page Content</p>',
            'order' => 1,
            'type' => ContentNodeType::PAGE->value
        ]);

        $this->browse(function (Browser $browser) use ($user, $manuscript) {
            $browser->visit('/')
                ->loginAs($user)
                ->visit("/studio/manuscript/{$manuscript->slug}")
                ->waitFor('header h1', 30)
                ->assertSeeIn('header h1', $manuscript->title)
                ->assertSee('Entity Studio');
        });
    }

    /**
     * Test Book Studio Access
     */
    public function test_can_access_book_studio(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create(['title' => 'Test Book']);

        // Book might need more complex setup, but let's try basic first
        $this->browse(function (Browser $browser) use ($user, $book) {
            $browser->visit('/')
                ->loginAs($user)
                ->visit("/studio/book/{$book->slug}")
                ->waitFor('header h1', 30)
                ->assertSeeIn('header h1', $book->title)
                ->assertSee('Entity Studio');
        });
    }
}
