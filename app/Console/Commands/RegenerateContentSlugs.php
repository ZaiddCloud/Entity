<?php

namespace App\Console\Commands;

use App\Models\AudioSegment;
use App\Models\VideoSegment;
use App\Models\ManuscriptPage;
use App\Models\BookChild;
use App\Helpers\SlugHelper;
use Illuminate\Console\Command;

class RegenerateContentSlugs extends Command
{
    protected $signature = 'content:regenerate-slugs';
    protected $description = 'Regenerate slugs for all content nodes (Segments, Pages, BookChildren) using SlugHelper';

    public function handle()
    {
        $this->info('Starting slug regeneration...');

        $models = [
            'AudioSegment' => AudioSegment::class,
            'VideoSegment' => VideoSegment::class,
            'ManuscriptPage' => ManuscriptPage::class,
            'BookChild' => BookChild::class,
        ];

        foreach ($models as $name => $modelClass) {
            $this->info("Processing {$name}...");
            $count = 0;

            $modelClass::chunk(100, function ($items) use (&$count, $name) {
                foreach ($items as $item) {
                    if ($item->title) {
                        $newSlug = SlugHelper::arabicSlug($item->title);
                        
                        // Ensure uniqueness
                        $originalSlug = $newSlug;
                        $counter = 1;
                        while ($item::where('slug', $newSlug)->where('_id', '!=', $item->_id)->exists()) {
                            $newSlug = $originalSlug . '-' . $counter;
                            $counter++;
                        }

                        $item->slug = $newSlug;
                        $item->save();
                        $count++;
                    }
                }
            });

            $this->info("✓ Regenerated {$count} {$name} slugs");
        }

        $this->info('✅ All slugs regenerated successfully!');
        return 0;
    }
}
