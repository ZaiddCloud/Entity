<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MongoIndexSeeder extends Seeder
{
    public function run(): void
    {
        $connection = app('db')->connection('mongodb');
        $db = $connection->getMongoDB();

        $this->command->info('Creating MongoDB indexes...');

        // Manuscript Pages
        $db->manuscript_pages->createIndex(
            ['manuscript_id' => 1, 'order' => 1],
            ['name' => 'manuscript_hierarchy']
        );
        $db->manuscript_pages->createIndex(
            ['manuscript_id' => 1, 'slug' => 1],
            ['name' => 'manuscript_slug', 'unique' => true]
        );

        // Audio Segments
        $db->audio_segments->createIndex(
            ['audio_id' => 1, 'order' => 1],
            ['name' => 'audio_hierarchy']
        );

        // Video Segments
        $db->video_segments->createIndex(
            ['video_id' => 1, 'order' => 1],
            ['name' => 'video_hierarchy']
        );

        $this->command->info('✅ Indexes created successfully!');
    }
}
