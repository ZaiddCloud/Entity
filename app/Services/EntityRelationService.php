<?php

namespace App\Services;

use App\Models\Entity;
use App\Models\Tag;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;
use RuntimeException;

class EntityRelationService
{
    /**
     * إرفاق tags لـ Entity مع validation ومعالجة الأخطاء
     */
    public function attachTags(Entity $entity, array $tagIds): void
    {
        $this->validateTagIds($tagIds);

        DB::transaction(function () use ($entity, $tagIds) {
            try {
                $entity->tags()->attach($tagIds);

                // تسجيل النشاط
                $this->logRelationChange($entity, 'tags_attached', [
                    'tag_ids' => $tagIds,
                    'tag_count' => count($tagIds)
                ]);

                // تحديث cache إذا كان موجوداً
                $this->clearEntityCache($entity);

            } catch (\Exception $e) {
                Log::error('Failed to attach tags to entity', [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'tag_ids' => $tagIds,
                    'error' => $e->getMessage()
                ]);

                throw new \RuntimeException('فشل في إرفاق الـ tags: ' . $e->getMessage());
            }
        });

        // تحديث العلاقات
        $entity->load('tags');
    }

    /**
     * فصل tags عن Entity
     */
    public function detachTags(Entity $entity, array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        DB::transaction(function () use ($entity, $tagIds) {
            try {
                $detachedCount = $entity->tags()->detach($tagIds);

                if ($detachedCount > 0) {
                    $this->logRelationChange($entity, 'tags_detached', [
                        'tag_ids' => $tagIds,
                        'detached_count' => $detachedCount
                    ]);

                    $this->clearEntityCache($entity);
                }

            } catch (\Exception $e) {
                Log::error('Failed to detach tags from entity', [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'tag_ids' => $tagIds,
                    'error' => $e->getMessage()
                ]);

                throw new RuntimeException('فشل في فصل الـ tags: ' . $e->getMessage());
            }
        });

        $entity->load('tags');
    }

    /**
     * مزامنة tags لـ Entity (يُزال القديم ويُضاف الجديد)
     */
    public function syncTags(Entity $entity, array $tagIds): void
    {
        $this->validateTagIds($tagIds);

        DB::transaction(function () use ($entity, $tagIds) {
            try {
                $changes = $entity->tags()->sync($tagIds);

                // تسجيل التغييرات
                if (!empty($changes['attached']) || !empty($changes['detached']) || !empty($changes['updated'])) {
                    $this->logRelationChange($entity, 'tags_synced', [
                        'attached' => $changes['attached'] ?? [],
                        'detached' => $changes['detached'] ?? [],
                        'updated' => $changes['updated'] ?? [],
                        'new_tag_ids' => $tagIds
                    ]);

                    $this->clearEntityCache($entity);
                }

            } catch (\Exception $e) {
                Log::error('Failed to sync tags for entity', [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'tag_ids' => $tagIds,
                    'error' => $e->getMessage()
                ]);

                throw new RuntimeException('فشل في مزامنة الـ tags: ' . $e->getMessage());
            }
        });

        $entity->load('tags');
    }

    /**
     * إرفاق tags بأسماء (إذا لم تكن موجودة، تُنشأ)
     */
    public function attachTagsByName(Entity $entity, array $tagNames): array
    {
        $tagIds = [];

        DB::transaction(function () use ($entity, $tagNames, &$tagIds) {
            foreach ($tagNames as $tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => \Illuminate\Support\Str::slug($tagName, '-', null)]
                );

                $tagIds[] = $tag->id;
            }

            if (!empty($tagIds)) {
                $entity->tags()->syncWithoutDetaching($tagIds);

                $this->logRelationChange($entity, 'tags_attached_by_name', [
                    'tag_names' => $tagNames,
                    'tag_ids' => $tagIds,
                    'created_tags' => count($tagIds) - count(array_unique($tagIds))
                ]);

                $this->clearEntityCache($entity);
            }
        });

        $entity->load('tags');

        return $tagIds;
    }

    /**
     * الحصول على tags مشتركة بين entities متعددة
     */
    public function getCommonTags(array $entities): array
    {
        if (count($entities) < 2) {
            return [];
        }

        $tagIdsByEntity = [];

        foreach ($entities as $entity) {
            if (!$entity instanceof Entity) {
                throw new InvalidArgumentException('جميع العناصر يجب أن تكون Entities');
            }

            $tagIdsByEntity[$entity->id] = $entity->tags()->pluck('tags.id')->toArray();
        }

        // إيجاد التقاطع
        $commonTagIds = array_intersect(...array_values($tagIdsByEntity));

        return Tag::whereIn('id', $commonTagIds)->get()->toArray();
    }

    /**
     * نسخ tags من entity إلى أخرى
     */
    public function copyTags(Entity $source, Entity $target, bool $replace = false): void
    {
        $sourceTagIds = $source->tags()->pluck('tags.id')->toArray();

        if ($replace) {
            $this->syncTags($target, $sourceTagIds);
        } else {
            $this->attachTags($target, $sourceTagIds);
        }

        $this->logRelationChange($source, 'tags_copied', [
            'source_id' => $source->id,
            'target_id' => $target->id,
            'tag_ids' => $sourceTagIds,
            'replace' => $replace
        ]);
    }

    /**
     * التحقق من صحة tag IDs
     */
    private function validateTagIds(array $tagIds): void
    {
        if (empty($tagIds)) {
            return;
        }

        // التحقق من أن جميع الـ IDs أرقام
        foreach ($tagIds as $tagId) {
            // إذا كنت تستخدم UUIDs
            if (!Str::isUuid($tagId)) {
                throw new InvalidArgumentException('tag ID غير صالح: ' . $tagId);
            }

            // التحقق من وجود الـ tags
            $existingCount = Tag::whereIn('id', $tagIds)->count();

            if ($existingCount !== count(array_unique($tagIds))) {
                $nonExistent = array_diff($tagIds, Tag::whereIn('id', $tagIds)->pluck('id')->toArray());
                throw new InvalidArgumentException('بعض الـ tags غير موجودة: ' . implode(', ', $nonExistent));
            }
        }
    }

    /**
     * تسجيل تغيير العلاقة
     */
    private function logRelationChange(Entity $entity, string $action, array $data = []): void
    {
        // يمكن استخدام Activity model أو Log
        if (class_exists(\App\Models\Activity::class)) {
            \App\Models\Activity::create([
                'entity_id' => $entity->id,
                'entity_type' => get_class($entity),
                'activity_type' => $action,
                'description' => $this->getActionDescription($action, $data),
                'user_id' => auth()->id() ?? null,
                'metadata' => $data
            ]);
        }

        Log::info('Entity relation changed', [
            'action' => $action,
            'entity_id' => $entity->id,
            'entity_type' => get_class($entity),
            'data' => $data
        ]);
    }

    /**
     * وصف الإجراء
     */
    private function getActionDescription(string $action, array $data): string
    {
        $descriptions = [
            'tags_attached' => 'تم إرفاق tags للعنصر',
            'tags_detached' => 'تم فصل tags عن العنصر',
            'tags_synced' => 'تم مزامنة tags للعنصر',
            'tags_attached_by_name' => 'تم إرفاق tags بالاسم للعنصر',
            'tags_copied' => 'تم نسخ tags من عنصر إلى آخر',
            // إضافة أوصاف للـ categories
            'categories_attached' => 'تم إرفاق categories للعنصر',
            'categories_detached' => 'تم فصل categories عن العنصر',
            'categories_synced' => 'تم مزامنة categories للعنصر',
            'categories_attached_by_name' => 'تم إرفاق categories بالاسم للعنصر',
        ];

        return $descriptions[$action] ?? 'تم تغيير علاقات العنصر';
    }

    /**
     * مسح cache الخاص بالـ entity
     */
    private function clearEntityCache(Entity $entity): void
    {
        // إذا كان هناك نظام cache
        if (function_exists('cache')) {
            $cacheKey = 'entity_' . get_class($entity) . '_' . $entity->id . '_relations';
            cache()->forget($cacheKey);
        }
    }

    /**
     * إرفاق categories لـ Entity
     */
    public function attachCategories(Entity $entity, array $categoryIds): void
    {
        $this->validateCategoryIds($categoryIds);

        DB::transaction(function () use ($entity, $categoryIds) {
            try {
                $entity->categories()->attach($categoryIds);

                // تسجيل النشاط
                $this->logRelationChange($entity, 'categories_attached', [
                    'category_ids' => $categoryIds,
                    'category_count' => count($categoryIds)
                ]);

                // تحديث cache
                $this->clearEntityCache($entity);

            } catch (\Exception $e) {
                Log::error('Failed to attach categories to entity', [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'category_ids' => $categoryIds,
                    'error' => $e->getMessage()
                ]);

                throw new RuntimeException('فشل في إرفاق الـ categories: ' . $e->getMessage());
            }
        });

        $entity->load('categories');
    }

    /**
     * فصل categories عن Entity
     */
    public function detachCategories(Entity $entity, array $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        DB::transaction(function () use ($entity, $categoryIds) {
            try {
                $detachedCount = $entity->categories()->detach($categoryIds);

                if ($detachedCount > 0) {
                    $this->logRelationChange($entity, 'categories_detached', [
                        'category_ids' => $categoryIds,
                        'detached_count' => $detachedCount
                    ]);

                    $this->clearEntityCache($entity);
                }

            } catch (\Exception $e) {
                Log::error('Failed to detach categories from entity', [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'category_ids' => $categoryIds,
                    'error' => $e->getMessage()
                ]);

                throw new RuntimeException('فشل في فصل الـ categories: ' . $e->getMessage());
            }
        });

        $entity->load('categories');
    }

    /**
     * مزامنة categories لـ Entity
     */
    public function syncCategories(Entity $entity, array $categoryIds): void
    {
        $this->validateCategoryIds($categoryIds);

        DB::transaction(function () use ($entity, $categoryIds) {
            try {
                $changes = $entity->categories()->sync($categoryIds);

                // تسجيل التغييرات
                if (!empty($changes['attached']) || !empty($changes['detached']) || !empty($changes['updated'])) {
                    $this->logRelationChange($entity, 'categories_synced', [
                        'attached' => $changes['attached'] ?? [],
                        'detached' => $changes['detached'] ?? [],
                        'updated' => $changes['updated'] ?? [],
                        'new_category_ids' => $categoryIds
                    ]);

                    $this->clearEntityCache($entity);
                }

            } catch (\Exception $e) {
                Log::error('Failed to sync categories for entity', [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'category_ids' => $categoryIds,
                    'error' => $e->getMessage()
                ]);

                throw new \RuntimeException('فشل في مزامنة الـ categories: ' . $e->getMessage());
            }
        });

        $entity->load('categories');
    }

    /**
     * إرفاق categories بأسماء
     */
    public function attachCategoriesByName(Entity $entity, array $categoryNames): array
    {
        $categoryIds = [];

        DB::transaction(function () use ($entity, $categoryNames, &$categoryIds) {
            foreach ($categoryNames as $categoryName) {
                $category = Category::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => \Illuminate\Support\Str::slug($categoryName, '-', null)]
                );

                $categoryIds[] = $category->id;
            }

            if (!empty($categoryIds)) {
                $entity->categories()->syncWithoutDetaching($categoryIds);

                $this->logRelationChange($entity, 'categories_attached_by_name', [
                    'category_names' => $categoryNames,
                    'category_ids' => $categoryIds,
                    'created_categories' => count($categoryIds) - count(array_unique($categoryIds))
                ]);

                $this->clearEntityCache($entity);
            }
        });

        $entity->load('categories');

        return $categoryIds;
    }

    /**
     * التحقق من صحة category IDs
     */
    private function validateCategoryIds(array $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        // التحقق من أن جميع الـ IDs أرقام
        foreach ($categoryIds as $categoryId) {
            if (!\Illuminate\Support\Str::isUuid($categoryId)) {
                throw new InvalidArgumentException('category ID غير صالح: ' . $categoryId);
            }
        }

        // التحقق من وجود الـ categories
        $existingCount = Category::whereIn('id', $categoryIds)->count();

        if ($existingCount !== count(array_unique($categoryIds))) {
            $nonExistent = array_diff(
                $categoryIds,
                Category::whereIn('id', $categoryIds)->pluck('id')->toArray()
            );
            throw new InvalidArgumentException('بعض الـ categories غير موجودة: ' . implode(', ', $nonExistent));
        }
    }
}
