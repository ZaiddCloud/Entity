# قائمة التغييرات الشاملة - تحسين Collections المنفصلة

## 📋 ملخص سريع

**الملفات الجديدة:** 7  
**الملفات المُعدّلة:** 3  
**الملفات المحذوفة:** 1  
**الوقت المقدر:** 7 ساعات

---

## 🆕 الملفات الجديدة (7 ملفات)

### 1. Models (3 ملفات)

#### A. `app/Models/ManuscriptPage.php`

```php
<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class ManuscriptPage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'manuscript_pages';
    protected $fillable = [
        'manuscript_id', 'slug', 'type', 'title', 'order',
        'content_blocks', 'metadata', 'last_updated',
        'folio_number', 'image_url', 'transcription_status',
    ];

    public function manuscript() {
        return $this->belongsTo(Manuscript::class, 'manuscript_id', 'id');
    }
}
```

#### B. `app/Models/AudioSegment.php`

```php
<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class AudioSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audio_segments';
    protected $fillable = [
        'audio_id', 'slug', 'type', 'title', 'order',
        'content_blocks', 'metadata', 'start_time', 'end_time',
    ];

    public function audio() {
        return $this->belongsTo(Audio::class, 'audio_id', 'id');
    }
}
```

#### C. `app/Models/VideoSegment.php`

```php
<?php
namespace App\Models;
use MongoDB\Laravel\Eloquent\Model;

class VideoSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'video_segments';
    protected $fillable = [
        'video_id', 'slug', 'type', 'title', 'order',
        'content_blocks', 'metadata', 'start_time', 'end_time',
    ];

    public function video() {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }
}
```

---

### 2. Observer (1 ملف)

#### `app/Observers/EntityContentObserver.php`

```php
<?php
namespace App\Observers;

use App\Models\Entity;
use App\Models\{BookChild, ManuscriptPage, AudioSegment, VideoSegment};

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

---

### 3. Seeders (2 ملفات)

#### A. `database/seeders/MongoIndexSeeder.php`

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
        $db->manuscript_pages->createIndex(['manuscript_id' => 1, 'order' => 1], ['name' => 'manuscript_hierarchy']);
        $db->manuscript_pages->createIndex(['manuscript_id' => 1, 'slug' => 1], ['name' => 'manuscript_slug', 'unique' => true]);

        // Audio Segments
        $db->audio_segments->createIndex(['audio_id' => 1, 'order' => 1], ['name' => 'audio_hierarchy']);

        // Video Segments
        $db->video_segments->createIndex(['video_id' => 1, 'order' => 1], ['name' => 'video_hierarchy']);

        $this->command->info('✅ Indexes created!');
    }
}
```

**التشغيل:**

```bash
php artisan db:seed --class=MongoIndexSeeder
```

---

#### B. Migration البيانات

**الملف:** `database/migrations/2026_01_07_000000_migrate_to_separated_collections.php`

**الوظيفة:**

-   نقل بيانات المخطوطات من `entity_contents` إلى `manuscript_pages`
-   نقل بيانات الصوتيات إلى `audio_segments`
-   نقل بيانات الفيديوهات إلى `video_segments`
-   الاحتفاظ بالبيانات القديمة (لا حذف)

**التشغيل:**

```bash
php artisan migrate
```

---

## ✏️ الملفات المُعدّلة (3 ملفات)

### 1. `app/Services/EntityContentService.php`

#### التغييرات المطلوبة:

**إضافة في أول الملف:**

```php
use App\Models\{BookChild, ManuscriptPage, AudioSegment, VideoSegment};
```

**إضافة دوال جديدة:**

```php
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
```

**تعديل الدوال الموجودة:**

-   `createNode()` - استخدام `getContentModel()` بدلاً من `EntityContent` مباشرة
-   `getNode()` - استخدام `getContentModel()` و `getEntityIdField()`
-   `getHierarchy()` - نفس الشيء
-   `getNavigation()` - نفس الشيء
-   `prepareEditorData()` - تبقى كما هي (تستخدم الدوال المحدثة)

**قبل:**

```php
public function createNode(Entity $entity, array $data)
{
    return EntityContent::create([
        'entity_id' => $entity->id,
        'entity_type' => strtolower(class_basename($entity)),
        ...
    ]);
}
```

**بعد:**

```php
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
```

---

### 2. `app/Providers/AppServiceProvider.php`

#### التغييرات المطلوبة:

**في أول الملف - إضافة:**

```php
use App\Observers\EntityContentObserver;
```

**في دالة `registerObservers()` - تعديل:**

**قبل:**

```php
protected function registerObservers(): void
{
    $lifecycleObserver = app(EntityLifecycleObserver::class);
    $auditObserver = app(EntityAuditObserver::class);
    $cacheObserver = app(EntityCacheObserver::class);

    $entityModels = [Book::class, Video::class, Audio::class, Manuscript::class];

    foreach ($entityModels as $modelClass) {
        $modelClass::observe($lifecycleObserver);
        $modelClass::observe($auditObserver);
        $modelClass::observe($cacheObserver);
    }

    Tag::observe($cacheObserver);
    Category::observe($cacheObserver);

    Book::observe(BookObserver::class); // ← سيتم حذفه
}
```

**بعد:**

```php
protected function registerObservers(): void
{
    $lifecycleObserver = app(EntityLifecycleObserver::class);
    $auditObserver = app(EntityAuditObserver::class);
    $cacheObserver = app(EntityCacheObserver::class);
    $contentObserver = app(EntityContentObserver::class); // ✅ جديد

    $entityModels = [Book::class, Video::class, Audio::class, Manuscript::class];

    foreach ($entityModels as $modelClass) {
        $modelClass::observe($lifecycleObserver);
        $modelClass::observe($auditObserver);
        $modelClass::observe($cacheObserver);
        $modelClass::observe($contentObserver); // ✅ جديد - يشمل الكل
    }

    Tag::observe($cacheObserver);
    Category::observe($cacheObserver);

    // ❌ تم حذف هذا السطر:
    // Book::observe(BookObserver::class);
}
```

---

### 3. `app/Console/Commands/SeedRealisticData.php`

#### التغييرات المطلوبة:

**السطور 226-299 - تعديل:**

**قبل:**

```php
\App\Models\EntityContent::create([
    'entity_id' => $entity->id,
    'entity_type' => 'book', // أو manuscript، audio، video
    'type' => 'chapter',
    'title' => 'الفصل الأول',
    ...
]);
```

**بعد:**

```php
$contentService = app(\App\Services\EntityContentService::class);
$contentService->createNode($entity, [
    'type' => 'chapter',
    'title' => 'الفصل الأول',
    'slug' => 'chapter-1',
    'order' => 1,
    ...
]);
```

**النتيجة:**

-   Books → تذهب لـ `book_children` ✅
-   Manuscripts → تذهب لـ `manuscript_pages` ✅
-   Audios → تذهب لـ `audio_segments` ✅
-   Videos → تذهب لـ `video_segments` ✅

---

## 🗑️ الملفات المحذوفة (1 ملف)

### `app/Observers/BookObserver.php`

**السبب:** تم دمجه في `EntityContentObserver`

**الحذف:**

```bash
rm app/Observers/BookObserver.php
```

**⚠️ متى تحذف؟** بعد التأكد من أن `EntityContentObserver` يعمل بشكل صحيح!

---

## 🗄️ MongoDB Collections

### قبل:

```
entity_contents (17M+ documents)
├── Books (تناقض!)
├── Manuscripts (~12M)
├── Audios
└── Videos

book_children (يعمل بشكل منفصل)
manuscript_children (غير مستخدم)
```

### بعد:

```
book_children (~200k documents) ✅
manuscript_pages (~12M documents) ✅
audio_segments (~50k documents) ✅
video_segments (~30k documents) ✅

entity_contents (يبقى للبيانات القديمة - backward compatibility)
manuscript_children (يمكن حذفه بعد التأكد)
```

---

## ✅ Acceptance Criteria (معايير القبول)

### 1. الـ Models

-   [ ] `ManuscriptPage::count()` يعيد عدد صحيح
-   [ ] `AudioSegment::where('audio_id', $id)->get()` يعمل
-   [ ] `VideoSegment::first()->video` يرجع Video model

### 2. الـ Observer

-   [ ] عند حذف `Book`، يحذف `BookChild` تلقائياً
-   [ ] عند حذف `Manuscript`، يحذف `ManuscriptPage` تلقائياً
-   [ ] عند حذف `Audio`، يحذف `AudioSegment` تلقائياً
-   [ ] عند حذف `Video`، يحذف `VideoSegment` تلقائياً

### 3. الـ Service

-   [ ] `createNode($book, [...])` ينشئ في `book_children`
-   [ ] `createNode($manuscript, [...])` ينشئ في `manuscript_pages`
-   [ ] `getNode($manuscript, 'page-1')` يجلب من `manuscript_pages`
-   [ ] `getHierarchy($manuscript)` يجلب من `manuscript_pages`

### 4. المحرر

-   [ ] `/editor/manuscript/{slug}` يعمل بدون مشاكل
-   [ ] Navigation (prev/next) يعمل
-   [ ] Hierarchy في Sidebar تظهر صحيحة
-   [ ] حفظ المحتوى يعمل

### 5. الأداء

-   [ ] البحث في المخطوطات: `< 100ms` (كان 2-5 ثواني)
-   [ ] تحميل صفحة واحدة: `< 50ms` (كان 500ms)
-   [ ] Indexes موجودة وتعمل

### 6. الـ Seeder

-   [ ] `php artisan project:seed-realistic` يعمل بدون أخطاء
-   [ ] Books تذهب لـ `book_children` (لا `entity_contents`)
-   [ ] Manuscripts تذهب لـ `manuscript_pages`
-   [ ] Audios تذهب لـ `audio_segments`
-   [ ] Videos تذهب لـ `video_segments`

---

## 📊 ملخص الأثر على المشروع

### الأجزاء التي **لا تحتاج** تغيير:

✅ Controllers (تستخدم Service - شفاف)  
✅ Frontend/Vue (نفس الـ API)  
✅ Routes (كما هي)  
✅ Entity Models (Book, Manuscript, Audio, Video)  
✅ EntityCacheObserver (لا تغيير)  
✅ EntityLifecycleObserver (لا تغيير)  
✅ EntityAuditObserver (لا تغيير)

### الأجزاء التي **تحتاج** تغيير:

📝 EntityContentService (تحديث)  
📝 AppServiceProvider (تحديث registerObservers)  
📝 SeedRealisticData (تحديث)  
🆕 3 Models جديدة  
🆕 1 Observer جديد  
🆕 2 Seeders جديدة  
🗑️ 1 Observer قديم (حذف)

---

## 🎯 الخلاصة

**مجموع الملفات المتأثرة: 11 ملف**

| النوع | العدد   |
| ----- | ------- |
| جديد  | 7 ملفات |
| تعديل | 3 ملفات |
| حذف   | 1 ملف   |

**الوقت المقدر: 7 ساعات**

**الفائدة: أداء أفضل 5-10x + تنظيم أفضل + صيانة أسهل** 🚀
