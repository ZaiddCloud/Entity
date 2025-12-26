<?php
// app/Observers/EntityAuditObserver.php

namespace App\Observers;

use App\Models\Book;
use App\Models\Video;
use App\Models\Audio;
use App\Models\Manuscript;
use Illuminate\Support\Facades\Log;

class EntityAuditObserver
{
    /**
     * Handle the entity "created" event.
     */
    public function created($entity): void
    {
        $this->logActivity($entity, 'created');
    }

    /**
     * Handle the entity "updated" event.
     */
    public function updated($entity): void
    {
        $this->logActivity($entity, 'updated', [
            'changes' => $entity->getChanges(),
        ]);
    }

    /**
     * Handle the entity "deleted" event.
     */
    public function deleted($entity): void
    {
        $this->logActivity($entity, 'deleted');
    }

    /**
     * Handle the entity "restored" event.
     */
    public function restored($entity): void
    {
        $this->logActivity($entity, 'restored');
    }

    /**
     * Handle the entity "force deleted" event.
     */
    public function forceDeleted($entity): void
    {
        $this->logActivity($entity, 'force_deleted');
    }

    /**
     * تسجيل النشاط
     */
    private function logActivity($entity, string $action, array $data = []): void
    {
        $entityType = $this->getEntityType($entity);

        Log::info("Entity {$action}", [
            'entity_type' => $entityType,
            'entity_id' => $entity->id ?? 'N/A',
            'title' => $entity->title ?? 'N/A',
            'action' => $action,
            'data' => $data,
            'timestamp' => now()->toISOString(),
        ]);
    }

    /**
     * الحصول على نوع الـ entity
     */
    private function getEntityType($entity): string
    {
        return match(true) {
            $entity instanceof Book => 'book',
            $entity instanceof Video => 'video',
            $entity instanceof Audio => 'audio',
            $entity instanceof Manuscript => 'manuscript',
            default => class_basename($entity),
        };
    }
}
