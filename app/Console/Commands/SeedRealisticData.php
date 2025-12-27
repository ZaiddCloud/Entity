<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Models\User;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;
use App\Models\Category;
use App\Models\Tag;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Note;
use App\Models\Collection;
use App\Models\Series;
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

        // 1. Core Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            ['name' => 'Admin User', 'password' => Hash::make('admin')]
        );
        if (User::count() < 5) User::factory(5)->create();
        $users = User::all();

        // 2. Core Taxonomies
        $categories = collect([
            'التاريخ العربي', 'علوم القرآن', 'الفقه وأصوله', 'الأدب والشعر',
            'الفلسفة والمنطق', 'المخطوطات القديمة', 'السيرة النبوية', 'الطب القديم'
        ])->map(fn($name) => Category::firstOrCreate(['name' => $name]));

        $tags = collect([
            'نادر', 'محقق', 'نسخة أصلية', 'العصر العباسي', 'الأندلس', 'ملون', 'مترجم'
        ])->map(fn($name) => Tag::firstOrCreate(['name' => $name]));

        // 3. Realistic Datasets
        $dataSets = [
            'book' => [
                'titles' => ['مقدمة ابن خلدون', 'صحيح البخاري', 'كتاب الحيوان للجاحظ', 'ديوان المتنبي', 'تهافت التهافت'],
                'authors' => ['ابن خلدون', 'البخاري', 'الجاحظ', 'المتنبي', 'ابن رشد']
            ],
            'manuscript' => [
                'titles' => ['مخطوط كليلة ودمنة (القرن الرابع)', 'رسالة الشافعي الأصلية', 'مصحف مذهب نادرا'],
                'authors' => ['ابن المقفع', 'الشافعي', 'مجهول']
            ],
            'audio' => [
                'titles' => ['شرح ألفية ابن مالك', 'تلاوات المنشاوي', 'محاضرة في التاريخ'],
                'authors' => ['ابن مالك', 'المنشاوي', 'د. السويدان']
            ],
            'video' => [
                'titles' => ['وثائقي العمارة الإسلامية', 'ندوة المخطوطات'],
                'authors' => ['الوثائقية', 'مركز التراث']
            ]
        ];

        $allEntities = collect();

        // 4. Seeding Entities (Main Loop)
        foreach ($dataSets as $type => $set) {
            $this->info("Seeding {$type}s...");
            $bar = $this->output->createProgressBar($count);
            $bar->start();

            for ($i = 0; $i < $count; $i++) {
                $modelClass = match($type) {
                    'book' => Book::class,
                    'manuscript' => Manuscript::class,
                    'audio' => Audio::class,
                    'video' => Video::class,
                };

                $title = $set['titles'][$i % count($set['titles'])] . " - نسخة " . (string)($i + 1);
                $author = $set['authors'][$i % count($set['authors'])] ?? 'كاتب مجهول';

                $attributes = [
                    'title' => $title,
                    'description' => "وصف تجريبي لـ {$title}. هذا العمل يعتبر ركيزة أساسية في مكتبتنا الرقمية.",
                ];

                if ($type === 'book') {
                    $attributes['author'] = $author;
                    $attributes['isbn'] = Str::random(13);
                } elseif ($type === 'manuscript') {
                    $attributes['author'] = $author;
                    $attributes['century'] = rand(1, 14);
                } else {
                    $attributes['duration'] = rand(300, 3600);
                }

                $entity = $modelClass::create($attributes);
                $allEntities->push($entity);

                // Relationships (Every entity MUST have these)
                $entity->categories()->attach($categories->random(1)->pluck('id'));
                $entity->tags()->attach($tags->random(rand(1, 3))->pluck('id'));

                // Interactions (Random but frequent)
                if (rand(1, 10) > 2) {
                    Comment::create([
                        'user_id' => $users->random()->id,
                        'entity_id' => $entity->id,
                        'entity_type' => $type,
                        'content' => "تعليق على {$title}."
                    ]);
                }

                if (rand(1, 10) > 4) {
                    Note::create([
                        'user_id' => $users->random()->id,
                        'entity_id' => $entity->id,
                        'entity_type' => $type,
                        'content' => "ملاحظة علمية خاصة بـ {$title}."
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

        // 5. Seeding Collections & Series (Overall grouping)
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
            $allEntities->random(rand(3, 5))->each(fn($e) => $col->addEntity($e));
        }

        $seriesTitles = ['سلسلة تاريخ الأندلس', 'روائع الأدب العربي'];
        foreach ($seriesTitles as $i => $title) {
            $series = Series::create([
                'title' => $title,
                'description' => "سلسلة مرتبة لـ $title",
                'order_column' => $i + 1
            ]);
            
            // Add 3 random entities to each series
            $allEntities->random(3)->each(fn($e, $idx) => $series->addEntity($e, $idx + 1));
        }

        $this->info("Seeding completed successfully with all relationships!");
    }
}
