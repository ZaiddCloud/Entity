<?php

namespace App\Observers;

use App\Models\Entity;
use App\Models\BookChild;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;

class EntityContentObserver
{
    /**
     * Handle the Entity "deleted" event.
     */
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
