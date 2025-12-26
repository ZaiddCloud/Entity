<?php
// app/Observers/EntityLifecycleObserver.php

namespace App\Observers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class EntityLifecycleObserver
{
    /**
     * Handle the entity "creating" event.
     */
    public function creating($entity): void
    {
        if ($this->shouldGenerateSlug($entity)) {
            $entity->slug = $this->generateUniqueSlug($entity->title, $entity);
        }
    }

    /**
     * Handle the entity "created" event.
     */
    public function created($entity): void
    {
        // الرسالة التي يتوقعها الاختبار
        Log::info('Entity created', [
            'type' => get_class($entity),
            'id' => $entity->id,
            'title' => $entity->title,
            'slug' => $entity->slug,
        ]);
    }

    /**
     * Handle the entity "updating" event.
     */
    public function updating($entity): void
    {
        if ($entity->isDirty('title') && !$entity->isDirty('slug')) {
            $entity->slug = $this->generateUniqueSlug($entity->title, $entity);
        }
    }

    /**
     * Handle the entity "updated" event.
     */
    public function updated($entity): void
    {
        if ($entity->getChanges()) {
            // الرسالة التي يتوقعها الاختبار
            Log::info('Entity updated', [
                'type' => get_class($entity),
                'id' => $entity->id,
                'changes' => $entity->getChanges(),
            ]);
        }
    }

    /**
     * Handle the entity "deleting" event.
     */
    public function deleting($entity): void
    {
        // الرسالة التي يتوقعها الاختبار
        Log::info('Entity deletion backup', [
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'data' => $entity->toArray(),
            'deleted_at' => now(),
            'deleted_by' => auth()->id() ?? null,
        ]);
    }

    /**
     * Handle the entity "deleted" event.
     */
    public function deleted($entity): void
    {
        // الرسالة التي يتوقعها الاختبار
        Log::info('Entity deleted notification', [
            'entity_type' => get_class($entity),
            'entity_id' => $entity->id,
            'event' => 'deleted',
        ]);
    }

    // ==================== Methods المساعدة ====================

    /**
     * تحقق إذا يجب إنشاء slug
     */
    private function shouldGenerateSlug($entity): bool
    {
        $hasSlug = in_array('slug', $entity->getFillable()) ||
            property_exists($entity, 'slug');

        $hasTitle = in_array('title', $entity->getFillable()) ||
            property_exists($entity, 'title');

        return $hasSlug && $hasTitle && !empty($entity->title) && empty($entity->slug);
    }

    /**
     * توليد slug فريد
     */
    private function generateUniqueSlug(string $title, $entity): string
    {
        $slug = Str::slug($title, '-', null);
        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug, $entity)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * تحقق إذا كان slug موجوداً
     */
    private function slugExists(string $slug, $entity): bool
    {
        $modelClass = get_class($entity);

        $query = $modelClass::where('slug', $slug);

        if ($entity->id) {
            $query->where('id', '!=', $entity->id);
        }

        return $query->exists();
    }
}
