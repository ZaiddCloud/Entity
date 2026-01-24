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
     * إرفاق tags لـ Entity
     */
    public function attachTags(Entity $entity, array $tagIds): void
    {
        $this->manageRelation($entity, 'tags', $tagIds, 'attach');
    }

    /**
     * فصل tags عن Entity
     */
    public function detachTags(Entity $entity, array $tagIds): void
    {
        $this->manageRelation($entity, 'tags', $tagIds, 'detach');
    }

    /**
     * مزامنة tags لـ Entity
     */
    public function syncTags(Entity $entity, array $tagIds): void
    {
        $this->manageRelation($entity, 'tags', $tagIds, 'sync');
    }

    /**
     * إرفاق categories لـ Entity
     */
    public function attachCategories(Entity $entity, array $categoryIds): void
    {
        $this->manageRelation($entity, 'categories', $categoryIds, 'attach');
    }

    /**
     * فصل categories عن Entity
     */
    public function detachCategories(Entity $entity, array $categoryIds): void
    {
        $this->manageRelation($entity, 'categories', $categoryIds, 'detach');
    }

    /**
     * مزامنة categories لـ Entity
     */
    public function syncCategories(Entity $entity, array $categoryIds): void
    {
        $this->manageRelation($entity, 'categories', $categoryIds, 'sync');
    }

    /**
     * دالة عامة لإدارة العلاقات (attach, detach, sync)
     */
    protected function manageRelation(Entity $entity, string $relation, array $ids, string $action): void
    {
        if (empty($ids) && $action !== 'sync') {
            return;
        }

        if ($action !== 'detach') {
            $this->validateIds($ids, $relation);
        }

        DB::transaction(function () use ($entity, $relation, $ids, $action) {
            try {
                $changes = $entity->{$relation}()->{$action}($ids);
                
                // تحديد ما إذا كان هناك تغيير فعلي للتسجيل
                $hasChanged = true;
                if ($action === 'sync') {
                    $hasChanged = !empty($changes['attached']) || !empty($changes['detached']) || !empty($changes['updated']);
                } elseif ($action === 'detach') {
                    $hasChanged = $changes > 0;
                }

                if ($hasChanged) {
                    $this->logRelationChange($entity, "{$relation}_{$action}ed", [
                        'relation' => $relation,
                        'ids' => $ids,
                        'changes' => $changes
                    ]);

                    $this->clearEntityCache($entity);
                }
            } catch (\Exception $e) {
                Log::error("Failed to {$action} {$relation} for entity", [
                    'entity_id' => $entity->id,
                    'entity_type' => get_class($entity),
                    'ids' => $ids,
                    'error' => $e->getMessage()
                ]);

                throw new RuntimeException("فشل في {$action} الـ {$relation}: " . $e->getMessage());
            }
        });

        $entity->load($relation);
    }

    /**
     * إرفاق tags بأسماء (إذا لم تكن موجودة، تُنشأ)
     */
    public function attachTagsByName(Entity $entity, array $tagNames): array
    {
        return $this->attachByNames($entity, 'tags', $tagNames, Tag::class);
    }

    /**
     * إرفاق categories بأسماء
     */
    public function attachCategoriesByName(Entity $entity, array $categoryNames): array
    {
        return $this->attachByNames($entity, 'categories', $categoryNames, Category::class);
    }

    /**
     * دالة عامة لإلحاق العلاقات بالأسماء
     */
    protected function attachByNames(Entity $entity, string $relation, array $names, string $modelClass): array
    {
        $ids = [];

        DB::transaction(function () use ($entity, $relation, $names, $modelClass, &$ids) {
            foreach ($names as $name) {
                $model = $modelClass::firstOrCreate(
                    ['name' => $name],
                    ['slug' => Str::slug($name, '-', null)]
                );
                $ids[] = $model->id;
            }

            if (!empty($ids)) {
                $entity->{$relation}()->syncWithoutDetaching($ids);
                $this->logRelationChange($entity, "{$relation}_attached_by_name", [
                    'names' => $names,
                    'ids' => $ids
                ]);
                $this->clearEntityCache($entity);
            }
        });

        $entity->load($relation);
        return $ids;
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

        return Tag::query()->whereIn('id', $commonTagIds)->get()->toArray();
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
     * التحقق من صحة الـ IDs
     */
    private function validateIds(array $ids, string $relation): void
    {
        if (empty($ids)) {
            return;
        }

        foreach ($ids as $id) {
            if (!Str::isUuid($id)) {
                throw new InvalidArgumentException("{$relation} ID غير صالح: " . $id);
            }
        }

        $modelClass = $relation === 'tags' ? Tag::class : Category::class;
        $existingCount = $modelClass::whereIn('id', $ids)->count();

        if ($existingCount !== count(array_unique($ids))) {
            $existingIds = $modelClass::whereIn('id', $ids)->pluck('id')->toArray();
            $nonExistent = array_diff($ids, $existingIds);
            throw new InvalidArgumentException("بعض الـ {$relation} غير موجودة: " . implode(', ', $nonExistent));
        }
    }

    /**
     * تسجيل تغيير العلاقة
     */
    private function logRelationChange(Entity $entity, string $action, array $data = []): void
    {
        if (class_exists(\App\Models\Activity::class)) {
            \App\Models\Activity::query()->create([
                'entity_id' => $entity->id,
                'entity_type' => get_class($entity),
                'activity_type' => $action,
                'description' => $this->getActionDescription($action, $data),
                'user_id' => auth()->guard()->id() ?? \App\Models\User::query()->first()?->id,
                'changes' => $data
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
        if (function_exists('cache')) {
            $cacheKey = 'entity_' . get_class($entity) . '_' . $entity->id . '_relations';
            cache()->forget($cacheKey);
        }
    }
}
