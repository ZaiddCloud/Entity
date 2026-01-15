<?php

namespace App\Console\Commands;

use App\Models\Audio;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncStorage extends Command
{
    protected $signature = 'project:sync-storage {path? : Optional external path to sync}';
    protected $description = 'Scan storage directory (or external path via symlink) and register new media files';

    public function handle()
    {
        $targetPath = $this->argument('path');

        // Default: Scan internal storage
        if (!$targetPath) {
            $rootPath = storage_path('app/public');
            $this->scanDirectory($rootPath, '');
        } else {
            // External Path Logic
            if (!File::exists($targetPath)) {
                $this->error("External path not found: $targetPath");
                return;
            }

            // 1. Create Symlink Directory if not exists
            $importsDir = storage_path('app/public/imports');
            if (!File::exists($importsDir)) {
                File::makeDirectory($importsDir, 0755, true);
            }

            // 2. Determine Link Name
            $folderName = basename($targetPath);
            $linkPath = "$importsDir/$folderName";

            // 3. Create Symlink if not exists
            if (!is_link($linkPath)) {
                try {
                    symlink($targetPath, $linkPath);
                    $this->info("Created symlink: imports/$folderName -> $targetPath");
                } catch (\Exception $e) {
                    $this->error("Failed to create symlink. Check permissions.");
                    return;
                }
            } else {
                $this->info("Using existing symlink: imports/$folderName");
            }

            // 4. Scan the SYMLINKED path (to get relative web paths)
            $this->scanDirectory($linkPath, "imports/$folderName");
        }

        $this->info("Sync complete!");
    }

    protected function scanDirectory($directory, $baseRelativePath)
    {
        $this->info("Scanning: $directory");

        // Audio Extensions
        $audioExts = ['mp3', 'm4a', 'wav', 'ogg'];
        // Video Extensions
        $videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm'];

        $allFiles = File::allFiles($directory);
        $count = 0;

        foreach ($allFiles as $file) {
            $ext = strtolower($file->getExtension());
            // Calculate relative path for DB/Web (e.g. imports/drive/file.mp3)
            // If baseRelativePath is set, we need to be careful. 
            // File::allFiles returns absolute paths or SplFileInfo.
            // We want the path relative to storage/app/public.

            if ($baseRelativePath) {
                // For external symlinks: baseRelativePath + relative path inside the target
                $relativePathInside = $file->getRelativePathname();
                $relativePath = $baseRelativePath . '/' . $relativePathInside;
                // Clean double slashes
                $relativePath = str_replace('//', '/', $relativePath);
            } else {
                // For internal storage
                $relativePath = $file->getRelativePathname();
            }

            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

            // 1. Audio
            if (in_array($ext, $audioExts)) {
                if (Audio::where('file_path', $relativePath)->exists())
                    continue;

                Audio::create([
                    'id' => (string) Str::uuid(),
                    'title' => $this->cleanTitle($filename),
                    'slug' => Str::slug($filename) . '-' . Str::random(6),
                    'file_path' => $relativePath,
                    'type' => 'audio',
                    'author_id' => 1
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

        $this->info("Registered $count new files in this batch.");
    }

    protected function cleanTitle($filename)
    {
        // Remove common patterns like date prefixes if desired, or keep raw
        // Keeping it simple for now, maybe replacing underscores
        return str_replace(['_', '-'], ' ', $filename);
    }
}
