<?php

namespace App\Services;

use App\Models\User;
use App\Models\Entity;
use App\Models\ReadingPosition;

class ReadingPositionService
{
    /**
     * Get the saved reading position for a user and entity.
     */
    public function getPosition(User $user, Entity $entity): ?ReadingPosition
    {
        return ReadingPosition::where('user_id', $user->id)
            ->where('entity_id', $entity->id)
            ->where('entity_type', $entity->getMorphClass())
            ->first();
    }

    /**
     * Save or update the reading position for a user and entity.
     */
    public function savePosition(User $user, Entity $entity, array $data): ReadingPosition
    {
        return ReadingPosition::updateOrCreate(
            [
                'user_id' => $user->id,
                'entity_id' => $entity->id,
                'entity_type' => $entity->getMorphClass(),
            ],
            [
                'node_slug' => $data['node_slug'] ?? null,
                'scroll_offset' => $data['scroll_offset'] ?? 0,
                'timestamp' => $data['timestamp'] ?? null,
            ]
        );
    }
}
