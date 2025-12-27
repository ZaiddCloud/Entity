<?php
// app/Observers/EntityCacheObserver.php

namespace App\Observers;

use App\Models\Entity;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;

class EntityCacheObserver
{
    protected static array $processing = [];

    protected function isProcessing(Model $model, string $task): bool
    {
        $key = get_class($model) . ':' . ($model->id ?? 'new') . ':' . $task;
        return isset(static::$processing[$key]);
    }

    protected function startProcessing(Model $model, string $task): void
    {
        $key = get_class($model) . ':' . ($model->id ?? 'new') . ':' . $task;
        static::$processing[$key] = true;
    }

    protected function stopProcessing(Model $model, string $task): void
    {
        $key = get_class($model) . ':' . ($model->id ?? 'new') . ':' . $task;
        unset(static::$processing[$key]);
    }

    /**
     * Handle the Model "created" event.
     */
    protected static int $counter = 0;

    public function created($model): void
    {
        if ($this->isProcessing($model, 'all')) return;
        $this->startProcessing($model, 'all');

        try {
            $this->invalidateGeneralCaches($model);
            $this->warmEntityCache($model);
        } finally {
            $this->stopProcessing($model, 'all');
        }
    }

    public function updated($model): void
    {
        if ($this->isProcessing($model, 'all')) return;
        $this->startProcessing($model, 'all');

        try {
            $this->invalidateGeneralCaches($model);
            $this->invalidateSpecificCaches($model);
            $this->warmEntityCache($model);
        } finally {
            $this->stopProcessing($model, 'all');
        }
    }

    public function deleted($model): void
    {
        if ($this->isProcessing($model, 'all')) return;
        $this->startProcessing($model, 'all');

        try {
            $this->invalidateGeneralCaches($model);
            $this->invalidateSpecificCaches($model);
        } finally {
            $this->stopProcessing($model, 'all');
        }
    }

    public function restored($model): void
    {
        if ($this->isProcessing($model, 'all')) return;
        $this->startProcessing($model, 'all');

        try {
            $this->invalidateGeneralCaches($model);
            $this->warmEntityCache($model);
        } finally {
            $this->stopProcessing($model, 'all');
        }
    }

    public function forceDeleted($model): void
    {
        if ($this->isProcessing($model, 'all')) return;
        $this->startProcessing($model, 'all');

        try {
            $this->invalidateGeneralCaches($model);
            $this->invalidateSpecificCaches($model);
        } finally {
            $this->stopProcessing($model, 'all');
        }
    }

    /**
     * حذف الكاشات العامة
     */
    protected function invalidateGeneralCaches($model): void
    {
        $modelClass = class_basename($model);
        $pluralModelClass = str($modelClass)->plural()->toString();

        // حذف كاشات النموذج العام
        Cache::forget("{$pluralModelClass}.all");
        Cache::forget("{$pluralModelClass}.recent");
        Cache::forget("{$pluralModelClass}.popular");

        // إذا كان Entity، أحذف كاشات Entities العامة
        if ($model instanceof Entity) {
            Cache::forget('entities.all');
            Cache::forget('entities.recent');
            Cache::forget('entities.popular');

            $entityType = class_basename($model);
            Cache::forget("entities.{$entityType}.all");
            Cache::forget("entities.{$entityType}.recent");
            Cache::forget("entities.{$entityType}.popular");
        }
    }

    /**
     * حذف الكاشات الخاصة بالنموذج
     */
    protected function invalidateSpecificCaches($model): void
    {
        $modelClass = class_basename($model);
        $modelId = $model->getKey();

        // حذف كاش النموذج نفسه
        Cache::forget("{$modelClass}.{$modelId}");

        // إذا كان Entity، أحذف كاشاته الإضافية
        if ($model instanceof Entity) {
            $entityType = class_basename($model);

            Cache::forget("entity.{$entityType}.{$modelId}");
            Cache::forget("entity.{$entityType}.{$modelId}.with_relations");
            Cache::forget("entity.{$entityType}.{$modelId}.stats");

            // حذف كاشات العلاقات
            $this->invalidateRelationCaches($model);
        }
    }

    /**
     * حذف كاشات العلاقات
     */
    public function invalidateRelationCaches(Entity $entity): void
    {
        // Tags
        if (method_exists($entity, 'tags')) {
            // حذف الكاش العام للتاجات
            Cache::forget('tags.entities.all');

            // حذف كاشات التاجات المرتبطة (إذا كانت محمّلة)
            if ($entity->relationLoaded('tags')) {
                foreach ($entity->tags as $tag) {
                    Cache::forget("tag.{$tag->id}.entities");
                    Cache::forget("tag.{$tag->id}.entities.all");
                }
            }
        }

        // Categories
        if (method_exists($entity, 'categories')) {
            // حذف الكاش العام للفئات
            Cache::forget('categories.entities.all');

            // حذف كاشات الفئات المرتبطة (إذا كانت محمّلة)
            if ($entity->relationLoaded('categories')) {
                foreach ($entity->categories as $category) {
                    Cache::forget("category.{$category->id}.entities");
                    Cache::forget("category.{$category->id}.entities.all");
                    Cache::forget("category.{$category->id}.hierarchy");
                }
            }
        }
    }

    /**
     * تدفئة كاش الـ Entity
     */
    protected function warmEntityCache($model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        $entityType = class_basename($model);
        $entityId = $model->getKey();

        // تدفئة كاش الـ Entity الأساسي
        Cache::remember(
            "entity.{$entityType}.{$entityId}",
            now()->addHours(1),
            fn() => $model->fresh()
        );

        // تدفئة كاش الـ Entity مع العلاقات
        Cache::remember(
            "entity.{$entityType}.{$entityId}.with_relations",
            now()->addHours(1),
            function () use ($model) {
                $relations = [];

                if (method_exists($model, 'tags')) {
                    $relations[] = 'tags';
                }

                if (method_exists($model, 'categories')) {
                    $relations[] = 'categories';
                }

                if (method_exists($model, 'activities')) {
                    $relations[] = 'activities';
                }

                return $model->load($relations);
            }
        );

        // تدفئة كاش الإحصائيات
        Cache::remember(
            "entity.{$entityType}.{$entityId}.stats",
            now()->addMinutes(30),
            fn() => $this->generateEntityStats($model)
        );
    }

    /**
     * توليد إحصائيات الـ Entity
     */
    protected function generateEntityStats(Entity $entity): array
    {
        return [
            'tags_count' => method_exists($entity, 'tags') ? $entity->tags()->count() : 0,
            'categories_count' => method_exists($entity, 'categories') ? $entity->categories()->count() : 0,
            'last_activity' => method_exists($entity, 'activities') ?
                $entity->activities()->latest()->first()?->created_at : null,
            // القيم التالية صفر لأن العلاقات معلقة حالياً
            'views_count' => 0,
            'favourites_count' => 0,
            'comments_count' => 0,
            'reviews_count' => 0,
            'average_rating' => 0,
        ];
    }

    /**
     * حذف كاش جميع الـ Entities
     */
    public function clearEntityCache(): void
    {
        $keys = [
            'entities.recent',
            'entities.popular',
            'entities.all',
        ];

        // حذف كاشات جميع أنواع الـ Entities
        $entityTypes = ['Book', 'Video', 'Audio', 'Manuscript'];
        foreach ($entityTypes as $type) {
            $keys[] = "entities.{$type}.all";
            $keys[] = "entities.{$type}.recent";
            $keys[] = "entities.{$type}.popular";
        }

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
