<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EntityContent;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;

return new class extends Migration
{
    public function up(): void
    {
        // Cleanup first to avoid duplicates on re-runs
        ManuscriptPage::truncate();
        AudioSegment::truncate();
        VideoSegment::truncate();

        // 1. Manuscripts
        $this->migrateManuscripts();

        // 2. Audios
        $this->migrateAudios();

        // 3. Videos
        $this->migrateVideos();
    }

    protected function migrateManuscripts(): void
    {
        EntityContent::where('entity_type', 'manuscript')
            ->chunkById(500, function($contents) {
                foreach ($contents as $content) {
                    ManuscriptPage::create([
                        'manuscript_id' => $content->entity_id,
                        'slug' => $content->slug,
                        'type' => $content->type,
                        'title' => $content->title,
                        'order' => $content->order ?? 0,
                        'content_blocks' => $content->content_blocks ?? [],
                        'metadata' => $content->metadata ?? [],
                        'last_updated' => $content->last_updated ?? now(),
                        'folio_number' => $content->folio_number ?? null,
                        'image_url' => $content->image_url ?? null,
                        'transcription_status' => $content->transcription_status ?? null,
                    ]);
                }
            });
    }

    protected function migrateAudios(): void
    {
        EntityContent::where('entity_type', 'audio')
            ->chunkById(500, function($contents) {
                foreach ($contents as $content) {
                    AudioSegment::create([
                        'audio_id' => $content->entity_id,
                        'slug' => $content->slug,
                        'type' => $content->type,
                        'title' => $content->title,
                        'order' => $content->order ?? 0,
                        'content_blocks' => $content->content_blocks ?? [],
                        'metadata' => $content->metadata ?? [],
                        'last_updated' => $content->last_updated ?? now(),
                        'start_time' => $content->start_time ?? null,
                        'end_time' => $content->end_time ?? null,
                    ]);
                }
            });
    }

    protected function migrateVideos(): void
    {
        EntityContent::where('entity_type', 'video')
            ->chunkById(500, function($contents) {
                foreach ($contents as $content) {
                    VideoSegment::create([
                        'video_id' => $content->entity_id,
                        'slug' => $content->slug,
                        'type' => $content->type,
                        'title' => $content->title,
                        'order' => $content->order ?? 0,
                        'content_blocks' => $content->content_blocks ?? [],
                        'metadata' => $content->metadata ?? [],
                        'last_updated' => $content->last_updated ?? now(),
                        'start_time' => $content->start_time ?? null,
                        'end_time' => $content->end_time ?? null,
                    ]);
                }
            });
    }

    public function down(): void
    {
        $connection = app('db')->connection('mongodb');
        $db = $connection->getMongoDB();

        $db->dropCollection('manuscript_pages');
        $db->dropCollection('audio_segments');
        $db->dropCollection('video_segments');
    }
};
