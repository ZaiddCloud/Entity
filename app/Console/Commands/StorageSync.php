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

            // Check if record exists
            $exists = $modelClass::where('file_path', $filePath)->exists();

            if (!$exists || $this->option('force')) {
                $data = [
                    'title' => $title,
                    'slug' => $slug,
                    'file_path' => $filePath,
                    'description' => 'Automatically synced from storage.',
                ];

                // Add specific fields based on model
                if ($modelClass === Book::class) {
                    $data['author'] = 'Unknown';
                } elseif ($modelClass === Audio::class || $modelClass === Video::class) {
                    $data['format'] = $extension;
                    $data['duration'] = 0;
                } elseif ($modelClass === Manuscript::class) {
                    $data['author'] = 'Unknown';
                    $data['century'] = 0;
                }

                $modelClass::updateOrCreate(['file_path' => $filePath], $data);
                $count++;
                $this->line("  [+] Synced: {$title}");
            }
        }

        $this->info("Synced {$count} items for {$dir}.");
    }
}
