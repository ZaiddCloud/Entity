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

        // Generate Code for Manuscripts
        if (get_class($entity) === \App\Models\Manuscript::class && empty($entity->code)) {
            $entity->code = \App\Helpers\SlugHelper::uniqueCode(get_class($entity), $entity->title);
        }
    }

    /**
     * Handle the entity "updating" event.
     */
    public function updating($entity): void
    {
        // Slug generation is now handled by Entity boot() method if title is dirty
        // but we keep this as a secondary check or for manual updates
        if ($entity->isDirty('title') && !$entity->isDirty('slug')) {
            $entity->slug = $this->generateUniqueSlug($entity->title, $entity);
        }
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
        return \App\Helpers\SlugHelper::uniqueSlug(get_class($entity), $title);
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
