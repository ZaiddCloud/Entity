<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;
use App\Models\Author;
use App\Models\Publisher;
use App\Models\Booker;
use App\Models\Version;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Note;
use App\Models\Collection;
use App\Models\Series;
use App\Models\BookChild;
use App\Services\BookContentService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SeedRealisticData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:seed-realistic {--count=10 : The number of entities to create for each type}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with exhaustive and realistic Arabic data for testing and development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->option('count');
        $this->info("Starting exhaustive realistic data seeding (Count: {$count} for each type)...");

        // 0. Clear Existing Data
        $this->warn("Clearing existing data...");
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        Book::truncate();
        Audio::truncate();
        Video::truncate();
        Manuscript::truncate();
        Author::truncate();
        Publisher::truncate();
        Booker::truncate();
        Category::truncate();
        Tag::truncate();
        Activity::truncate();
        Comment::truncate();
        Note::truncate();
        Collection::truncate();
        Series::truncate();
        BookChild::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Core Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin User', 'password' => Hash::make('admin')]
        );
        if (User::count() < 5)
            User::factory(5)->create();
        $users = User::all();

        // 2. Core Taxonomies
        $categories = collect([
            'التاريخ العربي',
            'علوم القرآن',
            'الفقه وأصوله',
            'الأدب والشعر',
            'الفلسفة والمنطق',
            'المخطوطات القديمة',
            'السيرة النبوية',
            'الطب القديم'
        ])->map(fn($name) => Category::firstOrCreate(['name' => $name]));

        $tags = collect([
            'نادر',
            'محقق',
            'نسخة أصلية',
            'العصر العباسي',
            'الأندلس',
            'ملون',
            'مترجم'
        ])->map(fn($name) => Tag::firstOrCreate(['name' => $name]));

        // 3. New Ecosystem Data (Authors, Publishers, Bookers)
        $this->info("Seeding Authors, Publishers and Contributors...");

        $authorsList = ['ابن خلدون', 'البخاري', 'الجاحظ', 'المتنبي', 'ابن رشد', 'نجيب محفوظ', 'طه حسين', 'ابن المقفع', 'الشافعي', 'المنشاوي', 'د. السويدان'];
        $authors = collect($authorsList)->map(fn($name) => Author::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name, '-', null)]
        ));

        $publishersList = ['دار المعرفة', 'دار الشروق', 'مكتبة العبيكان', 'عالم المعرفة', 'مركز دراسات الوحدة العربية', 'مؤسسة التراث', 'إذاعة القرآن الكريم'];
        $publishers = collect($publishersList)->map(fn($name) => Publisher::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name, '-', null)]
        ));

        $bookersList = ['أحمد شاكر', 'محمد فؤاد عبد الباقي', 'ناصر الدين الألباني', 'بشار عواد معروف'];
        $bookers = collect($bookersList)->map(fn($name) => Booker::firstOrCreate(
            ['name' => $name],
            ['slug' => Str::slug($name, '-', null)]
        ));

        // 4. Realistic Datasets
        $dataSets = [
            'book' => [
                'items' => [
                    ['title' => 'مقدمة ابن خلدون', 'author' => 'ابن خلدون'],
                    ['title' => 'صحيح البخاري', 'author' => 'البخاري'],
                    ['title' => 'كتاب الحيوان', 'author' => 'الجاحظ'],
                    ['title' => 'ديوان المتنبي', 'author' => 'المتنبي'],
                    ['title' => 'تهافت التهافت', 'author' => 'ابن رشد'],
                ]
            ],
            'manuscript' => [
                'items' => [
                    ['title' => 'مخطوط كليلة ودمنة', 'author' => 'ابن المقفع'],
                    ['title' => 'رسالة الشافعي الأصلية', 'author' => 'الشافعي'],
                    ['title' => 'مصحف مذهب نادر', 'author' => 'مجهول'],
                ]
            ],
            'audio' => [
                'items' => [
                    ['title' => 'شرح ألفية ابن مالك', 'author' => 'ابن مالك'],
                    ['title' => 'تلاوات المنشاوي', 'author' => 'المنشاوي'],
                    ['title' => 'محاضرة في التاريخ', 'author' => 'د. السويدان'],
                ]
            ],
            'video' => [
                'items' => [
                    ['title' => 'وثائقي العمارة الإسلامية', 'author' => 'مركز التراث'],
                    ['title' => 'ندوة المخطوطات الدولية', 'author' => 'مؤسسة التراث'],
                ]
            ]
        ];

        $allEntities = collect();

        // 5. Seeding Entities (Main Loop)
        foreach ($dataSets as $type => $set) {
            $this->info("Seeding {$type}s...");
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            for ($i = 0; $i < $count; $i++) {
                $modelClass = match ($type) {
                    'book' => Book::class,
                    'manuscript' => Manuscript::class,
                    'audio' => Audio::class,
                    'video' => Video::class,
                };

                $itemData = $set['items'][$i % count($set['items'])];
                $title = $itemData['title'];

                // If we are creating more than the unique titles, add a suffix to avoid duplicate slugs
                if ($i >= count($set['items'])) {
                    $title .= " (" . (string) ($i + 1) . ")";
                }

                $attributes = [
                    'title' => $title,
                    'description' => "وصف تجريبي لـ {$title}. هذا العمل يعتبر ركيزة أساسية في مكتبتنا الرقمية ويوفر مادة علمية غنية للباحثين والقراء المهتمين بالتراث العربي والإسلامي.",
                ];

                if ($type === 'manuscript') {
                    $attributes['century'] = rand(1, 14);
                } elseif ($type === 'audio' || $type === 'video') {
                    $attributes['duration'] = rand(300, 3600);
                }

                $entity = $modelClass::create($attributes);
                $allEntities->push($entity);

                // --- NEW: MongoDB Digital Content Seeding (Comprehensive) ---
                if ($type === 'book') {
                    // Level 1: Sub-book (الكتاب الفرعي)
                    $subBookCount = rand(1, 2);
                    for ($sb = 1; $sb <= $subBookCount; $sb++) {
                        $subBook = \App\Models\EntityContent::create([
                            'entity_id' => $entity->id,
                            'entity_type' => 'book',
                            'type' => 'sub-book',
                            'title' => "كتاب " . ($sb === 1 ? 'المقدمات' : 'الأحكام'),
                            'content' => '<p>مقدمة للكتاب الفرعي...</p>',
                            'slug' => 'sub-book-' . $sb . '-' . substr($entity->slug, 0, 4),
                            'order' => $sb,
                        ]);

                        // Level 2: Part (الجزء)
                        $partCount = rand(1, 2);
                        for ($p = 1; $p <= $partCount; $p++) {
                            $part = \App\Models\EntityContent::create([
                                'entity_id' => $entity->id,
                                'entity_type' => 'book',
                                'parent_id' => $subBook->id,
                                'type' => 'part',
                                'title' => "الجزء {$p}",
                                'content' => '<p>مقدمة الجزء...</p>',
                                'slug' => 'part-' . $p . '-sb-' . $sb . '-' . substr($entity->slug, 0, 4),
                                'order' => $p
                            ]);

                            // Level 3: Chapter (الفصل) - For simplicity, skipping Bab level
                            $chapCount = rand(2, 4);
                            for ($c = 1; $c <= $chapCount; $c++) {
                                $chapter = \App\Models\EntityContent::create([
                                    'entity_id' => $entity->id,
                                    'entity_type' => 'book',
                                    'parent_id' => $part->id,
                                    'type' => 'chapter',
                                    'title' => "فصل {$c}: في المسائل المهمة",
                                    'content' => "<p>هذا هو محتوى الفصل رقم {$c}. يحتوي على نصوص وتفريعات.</p>",
                                    'slug' => 'chapter-' . $c . '-p-' . $p . '-' . substr($entity->slug, 0, 4),
                                    'order' => $c
                                ]);
                            }
                        }
                    }
                } elseif ($type === 'manuscript') {
                     // Create a dummy "Page" content for Manuscript
                     \App\Models\EntityContent::create([
                        'entity_id' => $entity->id,
                        'entity_type' => 'manuscript',
                        'type' => 'page',
                        'title' => 'الصفحة الأولى',
                        'slug' => 'page-1-' . substr($entity->slug, 0, 4),
                        'content' => '<p>محتوى الصفحة الأولى من المخطوطة...</p>',
                        'order' => 1,
                    ]);
                } elseif ($type === 'audio') {
                    // Create a dummy "Segment" for Audio
                     \App\Models\EntityContent::create([
                        'entity_id' => $entity->id,
                        'entity_type' => 'audio',
                        'type' => 'segment',
                        'title' => 'المقطع الأول',
                        'slug' => 'segment-1-' . substr($entity->slug, 0, 4),
                        'content' => '<p>تفريغ نصي للمقطع الأول...</p>',
                        'order' => 1,
                    ]);
                } elseif ($type === 'video') {
                    // Create a dummy "Scene" for Video
                     \App\Models\EntityContent::create([
                        'entity_id' => $entity->id,
                        'entity_type' => 'video',
                        'type' => 'scene',
                        'title' => 'المشهد الأول',
                        'slug' => 'scene-1-' . substr($entity->slug, 0, 4),
                        'content' => '<p>وصف ومحتوى المشهد الأول...</p>',
                        'order' => 1,
                    ]);
                }
                // --- END MongoDB Seeding ---

                // 6. Ecosystem Logic (Polymorphic Authors & Versions)

                // Attach Specific or Random Authors
                $authorName = $itemData['author'];
                $assignedAuthor = Author::where('name', $authorName)->first();
                if ($assignedAuthor) {
                    $entity->authors()->attach($assignedAuthor->id);
                } else {
                    $entity->authors()->attach($authors->random(rand(1, 2))->pluck('id'));
                }

                // Attach Contributors (Bookers) for Books and Manuscripts
                if (($type === 'book' || $type === 'manuscript') && rand(1, 10) > 4) {
                    $role = ($type === 'book') ? 'editor' : 'illustrator';
                    $entity->bookers()->attach($bookers->random()->id, ['role' => $role]);
                }

                // Create Version
                Version::create([
                    'versionable_id' => $entity->id,
                    'versionable_type' => $type, // Matches morphMap
                    'publisher_id' => $publishers->random()->id,
                    'isbn' => ($type === 'book') ? Str::random(13) : null,
                    'pages' => ($type === 'book' || $type === 'manuscript') ? rand(100, 1000) : null,
                    'published_year' => rand(1900, 2024),
                    'edition_number' => rand(1, 5),
                    'format' => match ($type) {
                        'book', 'manuscript' => 'pdf',
                        'audio' => 'mp3',
                        'video' => 'mp4',
                    },
                    'file_path' => null, // Placeholder
                ]);

                // Relationships (Every entity MUST have these)
                $entity->categories()->attach($categories->random(1)->pluck('id'));
                $entity->tags()->attach($tags->random(rand(1, 3))->pluck('id'));

                // Interactions (Random but frequent)
                if (rand(1, 10) > 2) {
                    Comment::create([
                        'user_id' => $users->random()->id,
                        'entity_id' => $entity->id,
                        'entity_type' => $type,
                        'content' => "تعليق ثري على {$title}، أنصح الجميع بالاطلاع عليه."
                    ]);
                }

                if (rand(1, 10) > 4) {
                    Note::create([
                        'user_id' => $users->random()->id,
                        'entity_id' => $entity->id,
                        'entity_type' => $type,
                        'content' => "ملاحظة علمية هامة تتعلق بالمحتوى الموجود في {$title}."
                    ]);
                }

                Activity::create([
                    'user_id' => $users->random()->id,
                    'entity_id' => $entity->id,
                    'entity_type' => $type,
                    'activity_type' => 'viewed',
                    'description' => "استعرض المستخدم {$title}"
                ]);

                $bar->advance();
            }
            $bar->finish();
            $this->newLine();
        }

        // 6. Seeding Collections & Series (Overall grouping)
        $this->info("Creating Collections and Series...");

        $collectionTitles = ['مجموعتي المفضلة', 'مراجعات أدبية', 'كنوز تراثية'];
        foreach ($collectionTitles as $name) {
            $col = Collection::create([
                'name' => $name,
                'user_id' => $users->random()->id,
                'description' => "مجموعة تضم مختارات من $name",
                'is_public' => true
            ]);

            // Add 3-5 random entities to each collection
            $allEntities->random(min(rand(3, 5), $allEntities->count()))->each(fn($e) => $col->addEntity($e));
        }

        $seriesTitles = ['سلسلة تاريخ الأندلس', 'روائع الأدب العربي'];
        foreach ($seriesTitles as $i => $title) {
            $series = Series::create([
                'title' => $title,
                'description' => "سلسلة مرتبة لـ $title",
                'order_column' => $i + 1
            ]);

            // Add 3 random entities to each series
            $allEntities->random(min(3, $allEntities->count()))->each(fn($e, $idx) => $series->addEntity($e, $idx + 1));
        }

        $this->info("Seeding completed successfully with all relationships!");
    }
}
