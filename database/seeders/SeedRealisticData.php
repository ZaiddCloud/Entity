<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Book;
use App\Models\BookChild;
use App\Models\Manuscript;
use App\Models\ManuscriptPage;
use App\Models\Audio;
use App\Models\AudioSegment;
use App\Models\Video;
use App\Models\VideoSegment;
use App\Enums\ContentNodeType;

class SeedRealisticData extends Seeder
{
    /**
     * Run the database seeds.
     * Step 8: Final Verification Data
     */
    public function run(): void
    {
        // 1. Create a Test User
        $user = User::firstOrCreate(
            ['email' => 'admin@entity.test'],
            ['name' => 'Admin User', 'password' => bcrypt('password')]
        );

        // 2. Book Hierarchy (The Standard)
        $book = Book::create([
            'title' => 'The Book of Knowledge',
            'slug' => 'book-of-knowledge',
            'type' => 'book'
        ]);

        $this->createBookHierarchy($book);

        // 3. Manuscript Hierarchy (The Super-Hybrid)
        $manuscript = Manuscript::create([
            'title' => 'Ancient Manuscript',
            'slug' => 'ancient-manuscript',
            'type' => 'manuscript'
        ]);

        $this->createManuscriptHierarchy($manuscript);

        // 4. Audio (Time-Based)
        $audio = Audio::create([
            'title' => 'Lecture Series',
            'slug' => 'lecture-series',
            'type' => 'audio',
            'file_path' => 'audio/sample.mp3'
        ]);

        $this->createAudioSegments($audio);

        // 5. Video (Time-Based)
        $video = Video::create([
            'title' => 'Cinema Masterpiece',
            'slug' => 'cinema-masterpiece',
            'type' => 'video',
            'file_path' => 'video/sample.mp4'
        ]);

        $this->createVideoSegments($video);
    }

    private function createBookHierarchy($book)
    {
        $levels = [
            ['type' => 'sub_book', 'title' => 'Volume 1'],
            ['type' => 'part', 'title' => 'Part 1'],
            ['type' => 'bab', 'title' => 'Chapter 1 (Bab)'],
            ['type' => 'chapter', 'title' => 'Section 1 (Fasl)'],
            ['type' => 'masalah', 'title' => 'Issue 1'],
            ['type' => 'section', 'title' => 'Point 1'],
        ];

        foreach ($levels as $index => $level) {
            BookChild::create([
                'book_id' => $book->id,
                'title' => $level['title'],
                'slug' => \Str::slug($level['title']),
                'type' => $level['type'],
                'order' => $index + 1,
                'content' => "<p>Content for {$level['title']}</p>"
            ]);
        }
    }

    private function createManuscriptHierarchy($manuscript)
    {
        // Hybrid Structure
        ManuscriptPage::create([
            'manuscript_id' => $manuscript->id,
            'title' => 'Introduction',
            'slug' => 'intro',
            'type' => 'part',
            'order' => 1,
            'content' => '<p>Introductory text.</p>'
        ]);

        // Markers (Folios/Pages)
        for ($i = 1; $i <= 3; $i++) {
            ManuscriptPage::create([
                'manuscript_id' => $manuscript->id,
                'title' => "Folio {$i}a",
                'slug' => "folio-{$i}a",
                'type' => 'folio',
                'order' => $i + 1,
                'content' => "<h4 class=\"structure-marker\" data-type=\"folio\" data-folio=\"{$i}\">Folio {$i}a:</h4><p>Text on folio {$i}a...</p>"
            ]);
        }
    }

    private function createAudioSegments($audio)
    {
        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Opening Theme',
            'slug' => 'opening',
            'type' => 'track',
            'start_time' => 0,
            'end_time' => 60,
            'order' => 1,
            'content' => '<h4 class="structure-marker" data-type="track" data-start-time="0">Opening Theme:</h4>'
        ]);

        AudioSegment::create([
            'audio_id' => $audio->id,
            'title' => 'Main Topic',
            'slug' => 'main-topic',
            'type' => 'segment',
            'start_time' => 60,
            'end_time' => 1200,
            'order' => 2,
            'content' => '<h4 class="structure-marker" data-type="segment" data-start-time="60">Main Topic:</h4>'
        ]);
    }

    private function createVideoSegments($video)
    {
        VideoSegment::create([
            'video_id' => $video->id,
            'title' => 'Scene 1: The Beginning',
            'slug' => 'scene-1',
            'type' => 'scene',
            'start_time' => 0,
            'end_time' => 300,
            'order' => 1,
            'content' => '<h4 class="structure-marker" data-type="scene" data-start-time="0">Scene 1:</h4>'
        ]);
    }
}
