<?php

namespace App\Console\Commands;

use App\Models\Audio;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncStorage extends Command
{
    protected $signature = 'project:sync-storage';
    protected $description = 'Scan storage directory and register new media files in database';

    public function handle()
    {
        $rootPath = storage_path('app/public');

        if (!File::exists($rootPath)) {
            $this->error("Storage path not found: $rootPath");
            return;
        }

        $this->info("Scanning storage: $rootPath");

        // Audio Extensions
        $audioExts = ['mp3', 'm4a', 'wav', 'ogg'];
        // Video Extensions
        $videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

        $allFiles = File::allFiles($rootPath);
        $count = 0;

        foreach ($allFiles as $file) {
            $ext = strtolower($file->getExtension());
            $relativePath = $file->getRelativePathname();
            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // 1. Audio
            if (in_array($ext, $audioExts)) {
                // Check if exists
                if (Audio::where('file_path', $relativePath)->exists())
                    continue;

                Audio::create([
                    'id' => (string) Str::uuid(),
                    'title' => $this->cleanTitle($filename),
                    'slug' => Str::slug($filename) . '-' . Str::random(6),
                    'file_path' => $relativePath,
                    'type' => 'audio',
                    'author_id' => 1 // Default author for now
                ]);
                $this->line("   + Registered Audio: $relativePath");
                $count++;
            }
            // 2. Video
            elseif (in_array($ext, $videoExts)) {
                if (Video::where('file_path', $relativePath)->exists())
                    continue;

                Video::create([
                    'id' => (string) Str::uuid(),
                    'title' => $this->cleanTitle($filename),
                    'slug' => Str::slug($filename) . '-' . Str::random(6),
                    'file_path' => $relativePath,
                    'type' => 'video',
                    'author_id' => 1
                ]);
                $this->line("   + Registered Video: $relativePath");
                $count++;
            }
        }

        $this->info("Sync complete! Registered $count new files.");
    }

    protected function cleanTitle($filename)
    {
        // Remove common patterns like date prefixes if desired, or keep raw
        // Keeping it simple for now, maybe replacing underscores
        return str_replace(['_', '-'], ' ', $filename);
    }
}
