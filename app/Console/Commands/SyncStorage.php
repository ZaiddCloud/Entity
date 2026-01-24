<?php

namespace App\Console\Commands;

use App\Models\Audio;
use App\Models\Video;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class SyncStorage extends Command
{
    protected $signature = 'storage:sync {path? : Optional external path to sync}';
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

        // Extensions
        $audioExts = ['mp3', 'm4a', 'wav', 'ogg'];
        $videoExts = ['mp4', 'mov', 'avi', 'mkv', 'webm'];
        $bookExts = ['md', 'txt', 'pdf'];
        $imageExts = ['jpg', 'jpeg', 'png', 'webp'];

        $allFiles = File::allFiles($directory);
        $count = 0;

        // Grouping for bundles (Manuscripts)
        $manuscriptFolders = [];

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
            $pathParts = explode('/', $file->getRelativePath()); // Directories as tags

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
            // 3. Books (Markdown/PDF)
            elseif (in_array($ext, $bookExts)) {
                 $media = \App\Models\Book::firstOrCreate(
                    ['file_path' => $relativePath],
                    [
                        'id' => (string) Str::uuid(),
                        'title' => $this->cleanTitle($filename),
                        'slug' => Str::slug($filename), // Simple slug for specific test case 'sira'
                        'type' => 'book',
                        'author_id' => 1,
                        'pages' => 0
                    ]
                );
            }
            // 4. Manuscript (Images in Folder) - Collect first, process later or real-time?
            // Test implication: 'manuscripts/Bundle_X/page1.jpg' -> Bundle_X is the Manuscript.
            elseif (in_array($ext, $imageExts) && str_contains($relativePath, 'manuscripts/')) {
                 $folderName = basename(dirname($fullPath));
                 $manuscriptFolders[$folderName][] = [
                     'file' => $file,
                     'relativePath' => $relativePath
                 ];
                 continue; // Skip individual file processing for now
            }

            // Sync Tags & Metadata
            if ($media) {
                 if ($media->wasRecentlyCreated) {
                    $this->line("   + Registered: $relativePath");
                    $count++;
                    
                    // Add tags from directory structure
                    foreach ($pathParts as $part) {
                         if (!empty($part) && !in_array($part, ['audio', 'video', 'books', 'audios', 'videos'])) {
                              $tag = \App\Models\Tag::firstOrCreate(['name' => $part, 'slug' => Str::slug($part)]);
                              $media->tags()->syncWithoutDetaching([$tag->id]);
                         }
                    }
                }
                
                // Audio/Video Metadata Updates... (Existing Logic)
                if (($media instanceof Audio || $media instanceof Video) && ($media->duration === 0 || !$media->bitrate)) {
                     $meta = $this->getMetadata($fullPath);
                     $updates = [];
                     if ($meta['duration'] > 0) $updates['duration'] = $meta['duration'];
                     if ($meta['bitrate'] > 0) $updates['bitrate'] = $meta['bitrate'];
                     if ($meta['format']) $updates['format'] = $meta['format'];
                     
                     if (!empty($updates)) $media->update($updates);
                }
            }
        }

        // Process Manuscripts
        foreach ($manuscriptFolders as $folderName => $pages) {
             $manuscript = \App\Models\Manuscript::firstOrCreate(
                  ['title' => $this->cleanTitle($folderName)],
                  [
                      'id' => (string) Str::uuid(),
                      'slug' => Str::slug($folderName),
                      'type' => 'manuscript',
                      'pages' => count($pages)
                  ]
             );
             
             // Setup pages
             foreach ($pages as $index => $pageData) {
                  \App\Models\ManuscriptPage::firstOrCreate(
                      ['image_url' => $pageData['relativePath']],
                      [
                          'manuscript_id' => $manuscript->id,
                          'slug' => Str::slug($folderName . '-page-' . ($index + 1)),
                          'title' => 'Page ' . ($index + 1),
                          'order' => $index + 1,
                          'type' => 'page'
                      ]
                  );
             }
        }

        $this->info("Scan processed $count files.");
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
