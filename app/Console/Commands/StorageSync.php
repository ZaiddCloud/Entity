<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;

class StorageSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:sync {--force : Overwrite existing records}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan storage directories and sync files with the database';

    /**
     * Mapping of directories to models and their allowed extensions.
     */
    protected $config = [
        'books' => [
            'model' => Book::class,
            'extensions' => ['pdf', 'epub', 'mobi', 'docx'],
        ],
        'audios' => [
            'model' => Audio::class,
            'extensions' => ['mp3', 'wav', 'm4a', 'aac'],
        ],
        'videos' => [
            'model' => Video::class,
            'extensions' => ['mp4', 'mkv', 'avi', 'mov'],
        ],
        'manuscripts' => [
            'model' => Manuscript::class,
            'extensions' => ['pdf', 'jpg', 'png', 'jpeg', 'tiff'],
        ],
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting storage synchronization...');

        foreach ($this->config as $dir => $settings) {
            $this->syncDirectory($dir, $settings['model'], $settings['extensions']);
        }

        $this->info('Synchronization completed successfully!');
    }

    /**
     * Sync a specific directory with its corresponding model.
     */
    protected function syncDirectory(string $dir, string $modelClass, array $extensions)
    {
        $this->comment("Scanning directory: storage/app/public/{$dir}");

        if (!Storage::disk('public')->exists($dir)) {
            Storage::disk('public')->makeDirectory($dir);
            $this->line("Created missing directory: {$dir}");
            return;
        }

        $files = Storage::disk('public')->files($dir);
        $count = 0;

        foreach ($files as $filePath) {
            $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

            if (!in_array($extension, $extensions)) {
                continue;
            }

            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $title = Str::headline($fileName);
            $slug = Str::slug($title);

            $fileName = pathinfo($filePath, PATHINFO_FILENAME);
            $title = Str::headline($fileName);
            $slug = Str::slug($title);

            // 1. Check if this VERSION already exists for this file
            $versionExists = \App\Models\Version::where('file_path', $filePath)->exists();

            if (!$versionExists || $this->option('force')) {
                // 2. Find or Create the abstract Entity (Book, Audio, Video, Manuscript)
                $entity = $modelClass::where('slug', $slug)->first();

                if (!$entity) {
                    $entity = $modelClass::create([
                        'slug' => $slug,
                        'title' => $title,
                        'description' => 'Automatically synced from storage.',
                        'file_path' => $filePath,
                    ]);
                } elseif ($this->option('force')) {
                    $entity->update([
                        'title' => $title,
                        'description' => 'Automatically synced from storage.',
                        'file_path' => $filePath,
                    ]);
                }

                // 3. Create/Update the Version (Polymorphic)
                \App\Models\Version::updateOrCreate(
                    ['file_path' => $filePath],
                    [
                        'versionable_id' => $entity->id,
                        'versionable_type' => $entity->type, // This uses the model's type attribute (audios, videos, etc.)
                        'format' => $extension,
                        'file_size' => Storage::disk('public')->size($filePath),
                        'edition_number' => 1,
                    ]
                );

                $count++;
                $this->line("  [+] Synced: {$title}");
            }
        }

        $this->info("Synced {$count} items for {$dir}.");
    }
}
