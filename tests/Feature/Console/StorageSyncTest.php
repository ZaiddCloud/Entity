<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use App\Models\Book;
use App\Models\BookChild;
use App\Models\Audio;
use App\Models\AudioSegment;
use App\Models\Video;
use App\Models\VideoSegment;
use App\Models\Manuscript;
use App\Models\ManuscriptPage;

class StorageSyncTest extends TestCase
{
    // Use RefreshDatabase if using SQL, but for Mongo we might need manual cleanup if not handled by trait
    // Assuming hybrid, let's just ensure we clean up

    protected function setUp(): void
    {
        parent::setUp();
        BookChild::truncate();
        ManuscriptPage::truncate();
        AudioSegment::truncate();
        VideoSegment::truncate();
        Book::query()->forceDelete();
        Audio::query()->forceDelete();
        Video::query()->forceDelete();
        Manuscript::query()->forceDelete();
        \App\Models\Version::truncate();
    }

    public function test_it_syncs_storage_files_recursively_and_supports_manuscript_bundles()
    {
        Storage::fake('public');

        // 1. Create Recursive Book Structure (Approach A: Categorization)
        Storage::disk('public')->put('books/History/Islamic/Sira.md', "# Sira\nBiography content.");

        // 2. Create Manuscript Bundle (Approach B: Single Entity Folder)
        Storage::disk('public')->put('manuscripts/Bundle_X/page1.jpg', 'img1');
        Storage::disk('public')->put('manuscripts/Bundle_X/page2.png', 'img2');

        // 3. Create Standalone Audio in nested folder
        Storage::disk('public')->put('audios/Podcasts/History/Episode1.mp3', 'mp3');

        // 2. Run Command
        $exitCode = $this->withoutMockingConsoleOutput()
            ->artisan('storage:sync', ['path' => Storage::disk('public')->path('')]);

        $this->assertEquals(0, $exitCode);

        // 3. Verify Recursive Book
        $book = Book::where('slug', 'sira')->first();
        $this->assertNotNull($book, 'Recursive Book should be created');
        // Tags should come from parent directories: History, Islamic
        $this->assertTrue($book->tags->contains('name', 'History'), 'Book should have History tag');
        $this->assertTrue($book->tags->contains('name', 'Islamic'), 'Book should have Islamic tag');

        // 4. Verify Manuscript Bundle
        $manuscript = Manuscript::where('slug', 'bundle-x')->first();
        $this->assertNotNull($manuscript, 'Manuscript Bundle should be created');
        // Should have 2 pages in its MongoDB collection
        $this->assertEquals(2, ManuscriptPage::where('manuscript_id', $manuscript->id)->count(), 'Manuscript should have 2 pages');

        // 5. Verify Nested Audio & Tags
        $audio = Audio::where('file_path', 'LIKE', '%Episode1.mp3')->first();
        $this->assertNotNull($audio, 'Nested Audio should be created');
        $this->assertTrue($audio->tags->contains('name', 'Podcasts'), 'Audio should have Podcasts tag');
    }
}
