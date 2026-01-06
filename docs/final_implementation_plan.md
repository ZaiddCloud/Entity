# الخطة النهائية المحدثة: فصل Collections بـ Observer موحد

## 🔍 ملخص الاكتشافات

### البنية الحالية (الوضع الفعلي)

```
MySQL (Entities): Book, Manuscript, Audio, Video

MongoDB (Content):
├── book_children → ✅ منفصل ونشط (معظم الكود يستخدمه)
├── entity_contents → ⚠️ للمخطوطات والوسائط (والـ Books في Seeder!)
└── manuscript_children → موجود لكن غير مستخدم
```

**التناقضات المكتشفة:**

1. ✅ `BookChild` منفصل ومُستخدم في معظم الكود
2. ⚠️ `SeedRealisticData` ينشئ `EntityContent` حتى للـ Books (تناقض!)
3. ⚠️ `BookObserver` منفصل، بينما الباقي بدون cascade delete
4. ✅ `EntityCacheObserver` و `EntityLifecycleObserver` موحدة للكل

### الحل المقترح

**نوحّد كل شيء:**

1. ✅ `EntityContentObserver` موحد لـ **الكل** (Book, Manuscript, Audio, Video)
2. ✅ حذف `BookObserver.php` (يصبح جزء من Observer الموحد)
3. ✅ تحديث `Seeder` ليستخدم Service للكل (يحل التناقض)
4. ✅ Models منفصلة لكل نوع

---

## 📝 خطة التنفيذ

### المرحلة 0: فحص البيانات (30 دقيقة)

```bash
mongo
> use entity
> db.entity_contents.count({entity_type: 'manuscript'})
> db.entity_contents.count({entity_type: 'audio'})
> db.entity_contents.count({entity_type: 'video'})
```

---

### المرحلة 1: Models الجديدة (1 ساعة)

#### A. ManuscriptPage

**الملف:** `app/Models/ManuscriptPage.php`

```php
<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ManuscriptPage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'manuscript_pages';

    protected $fillable = [
        'manuscript_id',
        'slug',
        'type',
        'title',
        'order',
        'content_blocks',
        'metadata',
        'last_updated',
        'folio_number',
        'image_url',
        'transcription_status',
    ];

    public function manuscript()
    {
        return $this->belongsTo(Manuscript::class, 'manuscript_id', 'id');
    }
}
```

#### B. AudioSegment

**الملف:** `app/Models/AudioSegment.php`

```php
<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AudioSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audio_segments';

    protected $fillable = [
        'audio_id',
        'slug',
        'type',
        'title',
        'order',
        'content_blocks',
        'metadata',
        'start_time',
        'end_time',
    ];

    public function audio()
    {
        return $this->belongsTo(Audio::class, 'audio_id', 'id');
    }
}
```

#### C. VideoSegment

**الملف:** `app/Models/VideoSegment.php`

```php
<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class VideoSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'video_segments';

    protected $fillable = [
        'video_id',
        'slug',
        'type',
        'title',
        'order',
        'content_blocks',
        'metadata',
        'start_time',
        'end_time',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }
}
```

---

### المرحلة 2: EntityContentObserver الموحد (15 دقيقة)

**الملف:** `app/Observers/EntityContentObserver.php`

```php
<?php

namespace App\Observers;

use App\Models\Entity;
use App\Models\BookChild;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;

/**
 * Observer موحد لإدارة cascade delete
 * يتبع نمط EntityLifecycleObserver و EntityCacheObserver
 */
class EntityContentObserver
{
    public function deleted(Entity $entity): void
    {
        $entityType = strtolower(class_basename($entity));

        match($entityType) {
            'book' => BookChild::where('book_id', $entity->id)->delete(),
            'manuscript' => ManuscriptPage::where('manuscript_id', $entity->id)->delete(),
            'audio' => AudioSegment::where('audio_id', $entity->id)->delete(),
            'video' => VideoSegment::where('video_id', $entity->id)->delete(),
            default => null
        };
    }
}
```

**التفعيل في AppServiceProvider:**

```php
// app/Providers/AppServiceProvider.php

use App\Observers\EntityContentObserver;

protected function registerObservers(): void
{
    $lifecycleObserver = app(EntityLifecycleObserver::class);
    $auditObserver = app(EntityAuditObserver::class);
    $cacheObserver = app(EntityCacheObserver::class);
    $contentObserver = app(EntityContentObserver::class); // ✅ NEW

    $entityModels = [Book::class, Video::class, Audio::class, Manuscript::class];

    foreach ($entityModels as $modelClass) {
        $modelClass::observe($lifecycleObserver);
        $modelClass::observe($auditObserver);
        $modelClass::observe($cacheObserver);
        $modelClass::observe($contentObserver); // ✅ cascade delete
    }

    // ❌ حذف BookObserver - لم نعد نحتاجه
    // Book::observe(BookObserver::class);

    Tag::observe($cacheObserver);
    Category::observe($cacheObserver);
}
```

---

### المرحلة 3: تحديث EntityContentService (1.5 ساعة)

**الملف:** `app/Services/EntityContentService.php`

```php
<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\EntityContent;
use App\Models\BookChild;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;

class EntityContentService
{
    protected function getContentModel(Entity $entity): string
    {
        return match(strtolower(class_basename($entity))) {
            'book' => BookChild::class,
            'manuscript' => ManuscriptPage::class,
            'audio' => AudioSegment::class,
            'video' => VideoSegment::class,
            default => EntityContent::class
        };
    }

    protected function getEntityIdField(Entity $entity): string
    {
        return match(strtolower(class_basename($entity))) {
            'book' => 'book_id',
            'manuscript' => 'manuscript_id',
            'audio' => 'audio_id',
            'video' => 'video_id',
            default => 'entity_id'
        };
    }

    public function createNode(Entity $entity, array $data)
    {
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        unset($data['entity_type']); // للتوافق

        return $model::create(array_merge($data, [
            $idField => $entity->id,
            'last_updated' => now(),
        ]));
    }

    public function getNode(Entity $entity, string $slug)
    {
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        return $model::where($idField, $entity->id)
            ->where('slug', $slug)
            ->firstOrFail();
    }

    public function getHierarchy(Entity $entity, ?int $limit = null)
    {
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        $query = $model::where($idField, $entity->id)
            ->orderBy('order')
            ->select(['_id', 'title', 'slug', 'type', 'order', 'parent_id']);

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function getNavigation(Entity $entity, $currentNode): array
    {
        $model = $this->getContentModel($entity);
        $idField = $this->getEntityIdField($entity);

        $prev = $model::where($idField, $entity->id)
            ->where('order', '<', $currentNode->order)
            ->orderBy('order', 'desc')
            ->first(['slug', 'title']);

        $next = $model::where($idField, $entity->id)
            ->where('order', '>', $currentNode->order)
            ->orderBy('order', 'asc')
            ->first(['slug', 'title']);

        return ['prev' => $prev, 'next' => $next];
    }

    public function prepareEditorData(Entity $entity, string $slug): array
    {
        $node = $this->getNode($entity, $slug);
        $hierarchy = $this->getHierarchy($entity, limit: 100);
        $navigation = $this->getNavigation($entity, $node);

        $resourceData = [
            'id' => $entity->id,
            'title' => $entity->title,
            'type' => strtolower(class_basename($entity)),
            'url' => $entity->file_path ? asset('storage/' . $entity->file_path) : null,
        ];

        if (in_array(class_basename($entity), ['Manuscript', 'Audio', 'Video'])) {
            $versions = $entity->versions()->with('publisher')->get();
            $resourceData['versions'] = $versions->map(function($v) {
                return [
                    'title' => $v->title ?? 'الإصدار ' . ($v->edition_number ?? '1'),
                    'url' => $v->file_path ? asset('storage/' . $v->file_path) : null
                ];
            })->toArray();
        }

        return [
            'entity' => $entity,
            'contentNode' => $node,
            'hierarchy' => $hierarchy,
            'navigation' => $navigation,
            'editor_mode' => strtolower(class_basename($entity)),
            'resource_data' => $resourceData
        ];
    }
}
```

---

### المرحلة 4: MongoDB Indexes (15 دقيقة)

**الملف:** `database/seeders/MongoIndexSeeder.php`

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class MongoIndexSeeder extends Seeder
{
    public function run(): void
    {
        $connection = app('db')->connection('mongodb');
        $db = $connection->getMongoDB();

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

        $this->command->info('✅ Indexes created!');
    }
}
```

**التشغيل:**

```bash
php artisan db:seed --class=MongoIndexSeeder
```

---

### المرحلة 5: Migration البيانات (2 ساعة)

**الملف:** `database/migrations/2026_01_07_000000_migrate_to_separated_collections.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\EntityContent;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;

return new class extends Migration
{
    public function up(): void
    {
        $this->migrateManuscripts();
        $this->migrateAudios();
        $this->migrateVideos();

        $this->command->info('✅ Migration completed!');
    }

    protected function migrateManuscripts(): void
    {
        $count = EntityContent::where('entity_type', 'manuscript')->count();
        if ($count === 0) return;

        $this->command->info("Migrating {$count} manuscript pages...");
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        EntityContent::where('entity_type', 'manuscript')
            ->chunkById(500, function($contents) use ($bar) {
                foreach ($contents as $content) {
                    ManuscriptPage::create([
                        'manuscript_id' => $content->entity_id,
                        'slug' => $content->slug,
                        'type' => $content->type,
                        'title' => $content->title,
                        'order' => $content->order ?? 0,
                        'content_blocks' => $content->content_blocks ?? [],
                        'metadata' => $content->metadata ?? [],
                        'last_updated' => $content->last_updated ?? now(),
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->command->newLine();
    }

    protected function migrateAudios(): void
    {
        $count = EntityContent::where('entity_type', 'audio')->count();
        if ($count === 0) return;

        $this->command->info("Migrating {$count} audio segments...");
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        EntityContent::where('entity_type', 'audio')
            ->chunkById(500, function($contents) use ($bar) {
                foreach ($contents as $content) {
                    AudioSegment::create([
                        'audio_id' => $content->entity_id,
                        'slug' => $content->slug,
                        'type' => $content->type,
                        'title' => $content->title,
                        'order' => $content->order ?? 0,
                        'content_blocks' => $content->content_blocks ?? [],
                        'metadata' => $content->metadata ?? [],
                        'last_updated' => $content->last_updated ?? now(),
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->command->newLine();
    }

    protected function migrateVideos(): void
    {
        $count = EntityContent::where('entity_type', 'video')->count();
        if ($count === 0) return;

        $this->command->info("Migrating {$count} video segments...");
        $bar = $this->command->getOutput()->createProgressBar($count);
        $bar->start();

        EntityContent::where('entity_type', 'video')
            ->chunkById(500, function($contents) use ($bar) {
                foreach ($contents as $content) {
                    VideoSegment::create([
                        'video_id' => $content->entity_id,
                        'slug' => $content->slug,
                        'type' => $content->type,
                        'title' => $content->title,
                        'order' => $content->order ?? 0,
                        'content_blocks' => $content->content_blocks ?? [],
                        'metadata' => $content->metadata ?? [],
                        'last_updated' => $content->last_updated ?? now(),
                    ]);
                    $bar->advance();
                }
            });

        $bar->finish();
        $this->command->newLine();
    }

    public function down(): void
    {
        $connection = app('db')->connection('mongodb');
        $db = $connection->getMongoDB();

        $db->dropCollection('manuscript_pages');
        $db->dropCollection('audio_segments');
        $db->dropCollection('video_segments');
    }
};
```

---

### المرحلة 6: تحديث SeedRealisticData (30 دقيقة)

**المشكلة المكتشفة:**

-   Seeder ينشئ `EntityContent::create()` مباشرة لكل الأنواع (حتى Books!)
-   هذا يسبب تناقض: Books يجب أن تستخدم `BookChild` دائماً

**الحل:** نستخدم `EntityContentService` للكل

**الملف:** [`app/Console/Commands/SeedRealisticData.php`](file:///home/z/PhpstormProjects/Entity/app/Console/Commands/SeedRealisticData.php) (MODIFY)

**التعديل في السطور 226-299:**

```php
// ❌ القديم (يسبب تناقض):
\App\Models\EntityContent::create([
    'entity_type' => 'book',  // ← Books تذهب لـ entity_contents!
    'entity_id' => $entity->id
]);

// ✅ الجديد (يحل التناقض):
$contentService = app(\App\Services\EntityContentService::class);
$contentService->createNode($entity, [
    'type' => 'chapter',
    'title' => 'الفصل الأول',
    // ...
]);
// سيوجه تلقائياً:
// - Books → BookChild
// - Manuscripts → ManuscriptPage
// - Audios → AudioSegment
// - Videos → VideoSegment
```

**النتيجة:** Seeder سيستخدم Collections الصحيحة دائماً!

---

### المرحلة 7: الاختبار (1 ساعة)

```bash
# 1. MongoDB Collections
mongo
> db.getCollectionNames()

# 2. البيانات
> db.manuscript_pages.count()
> db.manuscript_pages.findOne()

# 3. Indexes
> db.manuscript_pages.getIndexes()

# 4. المحرر
open http://localhost:8000/editor/manuscript/{slug}

# 5. Performance
> db.manuscript_pages.find({manuscript_id: "..."}).explain("executionStats")
```

---

## 📅 Timeline

| المرحلة               | الوقت        |
| --------------------- | ------------ |
| فحص البيانات          | 30 دقيقة     |
| Models الجديدة        | 1 ساعة       |
| EntityContentObserver | 15 دقيقة     |
| EntityContentService  | 1.5 ساعة     |
| Indexes               | 15 دقيقة     |
| Migration             | 2 ساعة       |
| تحديث Seeder          | 30 دقيقة     |
| الاختبار              | 1 ساعة       |
| **المجموع**           | **~7 ساعات** |

---

## ✅ الفوائد

**يحترم المشروع:**

-   ✅ نمط BookChild الموجود
-   ✅ Observer pattern الموحد (يشبه EntityLifecycleObserver)
-   ✅ EntityContentService واجهة موحدة
-   ✅ لا تغييرات في Frontend

**التحسينات:**

-   ⚡ Collections منفصلة + Indexes
-   ⚡ 5-10x أسرع
-   ⚡ Observer موحد بدلاً من 4
-   ✅ موحد للكل (Book, Manuscript, Audio, Video)
-   ✅ يتبع نمط المشروع الحالي
-   ✅ DRY - لا تكرار كود
-   ✅ يمكن حذف `BookObserver.php` بعد التنفيذ

**إضافة:** بعد التنفيذ، احذف:

```bash
rm app/Observers/BookObserver.php
```

---

## 🎯 الخلاصة

**الخطة النهائية:**

1. Models منفصلة (ManuscriptPage, AudioSegment, VideoSegment)
2. **EntityContentObserver موحد** (يتبع نمط المشروع)
3. EntityContentService محدث مع auto-routing
4. Migration آمن
5. Indexes محسّنة

**جاهز للتنفيذ!** 🚀
