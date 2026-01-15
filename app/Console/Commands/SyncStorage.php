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
            // Calculate relative path for DB/Web
            if ($baseRelativePath) {
                // For external symlinks: baseRelativePath + relative path inside the target
                $relativePathInside = $file->getRelativePathname();
                $relativePath = $baseRelativePath . '/' . $relativePathInside;
                $relativePath = str_replace('//', '/', $relativePath);
            } else {
                // For internal storage
                $relativePath = $file->getRelativePathname();
            }

            $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $fullPath = $file->getPathname();
            $media = null;

            // 1. Audio
            if (in_array($ext, $audioExts)) {
                $media = Audio::firstOrCreate(
                    ['file_path' => $relativePath],
                    [
                        'id' => (string) Str::uuid(),
                        'title' => $this->cleanTitle($filename),
                        'slug' => Str::slug($filename) . '-' . Str::random(6),
                        'type' => 'audio',
                        'author_id' => 1,
                        'duration' => 0
                    ]
                );
            }
            // 2. Video
            elseif (in_array($ext, $videoExts)) {
                $media = Video::firstOrCreate(
                    ['file_path' => $relativePath],
                    [
                        'id' => (string) Str::uuid(),
                        'title' => $this->cleanTitle($filename),
                        'slug' => Str::slug($filename) . '-' . Str::random(6),
                        'type' => 'video',
                        'author_id' => 1,
                        'duration' => 0
                    ]
                );
            }

            // Update Metadata (Duration, Bitrate, Size, etc.)
            if ($media) {
                if ($media->wasRecentlyCreated) {
                    $this->line("   + Registered: $relativePath");
                    $count++;
                }

                $updates = [];

                // 1. File Size
                if (!$media->file_size) {
                    $sizeBytes = File::size($fullPath);
                    $updates['file_size'] = round($sizeBytes / 1024); // KB
                }

                // 2. FFmpeg Metadata (Duration, Bitrate, Format)
                if ($media->duration === 0 || !$media->bitrate) {
                    $meta = $this->getMetadata($fullPath);

                    if ($meta['duration'] > 0)
                        $updates['duration'] = $meta['duration'];
                    if ($meta['bitrate'] > 0)
                        $updates['bitrate'] = $meta['bitrate'];
                    if ($meta['format'])
                        $updates['format'] = $meta['format'];
                }

                if (!empty($updates)) {
                    $media->update($updates);
                    $this->line("      > Updated Metadata: " . json_encode($updates));
                }
            }
        }

        $this->info("processed $count new files (Metadata refresh included).");
    }

    protected function cleanTitle($filename)
    {
        return str_replace(['_', '-'], ' ', $filename);
    }

    protected function getMetadata($filePath)
    {
        $meta = [
            'duration' => 0,
            'bitrate' => 0,
            'format' => ''
        ];

        try {
            $escapedPath = escapeshellarg($filePath);

            // Get Metadata using ffprobe
            // We ask for specific entries: duration, bit_rate, format_name
            // -v error: hide logs
            // -of default=noprint_wrappers=1:nokey=0: output as key=value pairs
            $cmd = "ffprobe -v error -show_entries format=duration,bit_rate,format_name -of default=noprint_wrappers=1:nokey=0 $escapedPath";
            $output = shell_exec($cmd);

            if ($output) {
                // Parse key=value lines
                $lines = explode("\n", trim($output));
                foreach ($lines as $line) {
                    if (str_contains($line, '=')) {
                        [$key, $val] = explode('=', trim($line), 2);

                        if ($key === 'duration') {
                            $meta['duration'] = (int) floatval($val);
                        } elseif ($key === 'bit_rate') {
                            $meta['bitrate'] = (int) (floatval($val) / 1000); // Convert to kbps
                        } elseif ($key === 'format_name') {
                            // ffprobe returns 'mp3,mp2' sometimes, take first
                            $meta['format'] = explode(',', $val)[0];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            // fail silently
        }
        return $meta;
    }
}
