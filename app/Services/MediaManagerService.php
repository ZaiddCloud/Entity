<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Entity;
use App\Models\Version;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class MediaManagerService
{
    protected EntityManagerService $entityManager;

    public function __construct(EntityManagerService $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Create a new media entity with its initial version and authors.
     *
     * @param array $data
     * @return Entity
     * @throws ValidationException
     */
    public function createMedia(array $data): Entity
    {
        $this->validateMediaData($data);

        return DB::transaction(function () use ($data) {
            // 1. Create the base entity using the generic service
            $entityData = [
                'title' => $data['title'],
                'type' => $data['type'], // book, video, audio, manuscript
                'description' => $data['description'] ?? null,
                'cover_path' => $data['cover_path'] ?? null,
                'file_path' => $data['file_path'] ?? null,
            ];

            // Add type-specific attributes
            if ($data['type'] === 'manuscript' && isset($data['century'])) {
                $entityData['century'] = $data['century'];
            } elseif (($data['type'] === 'audio' || $data['type'] === 'video') && isset($data['duration'])) {
                $entityData['duration'] = $data['duration'];
            }

            /** @var Entity $entity */
            $entity = $this->entityManager->create($entityData);

            // 2. Attach Authors
            if (!empty($data['author_ids'])) {
                $entity->authors()->sync($data['author_ids']);
            }

            // 3. Create the Initial Version
            $versionData = [
                'versionable_id' => $entity->id,
                'versionable_type' => $data['type'],
                'file_path' => $data['file_path'] ?? null, // This file_path is for the version, not the entity
                'publisher_id' => $data['publisher_id'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'pages' => $data['pages'] ?? null,
                'published_year' => $data['published_year'] ?? null,
                'edition_number' => $data['edition_number'] ?? 1,
                'format' => $data['format'] ?? ($data['type'] === 'book' ? 'pdf' : 'mp4'),
                'file_size' => $data['file_size'] ?? 0,
            ];

            Version::create($versionData);

            // Refresh to load relations
            return $entity->fresh(['authors', 'versions']);
        });
    }

    /**
     * Update an existing media entity and its related entities.
     *
     * @param Entity $entity
     * @param array $data
     * @return Entity
     * @throws ValidationException
     */
    public function updateMedia(Entity $entity, array $data): Entity
    {
        $this->validateMediaData($data);

        return DB::transaction(function () use ($entity, $data) {
            // 1. Update basic entity details
            $entityData = [
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
            ];

            if (isset($data['cover_path'])) {
                $entityData['cover_path'] = $data['cover_path'];
            }

            if (isset($data['file_path'])) {
                $entityData['file_path'] = $data['file_path'];
            }

            if ($entity->type === 'manuscript' && isset($data['century'])) {
                $entityData['century'] = $data['century'];
            } elseif (($entity->type === 'audio' || $entity->type === 'video') && isset($data['duration'])) {
                $entityData['duration'] = $data['duration'];
            }

            $this->entityManager->update($entity, $entityData);

            // 2. Sync Authors
            if (isset($data['author_ids'])) {
                $entity->authors()->sync($data['author_ids']);
            }

            // 3. Update or Create the Primary Version
            $versionData = [
                'publisher_id' => $data['publisher_id'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'pages' => $data['pages'] ?? null,
                'published_year' => $data['published_year'] ?? null,
                'edition_number' => $data['edition_number'] ?? 1,
            ];

            if (isset($data['file_path'])) {
                $versionData['file_path'] = $data['file_path'];
                $versionData['format'] = $data['format'] ?? 'pdf';
                $versionData['file_size'] = $data['file_size'] ?? 0;
            }

            $version = $entity->versions()->first();

            if ($version) {
                $version->update($versionData);
            } else {
                if (isset($data['file_path'])) {
                    $versionData['versionable_id'] = $entity->id;
                    $versionData['versionable_type'] = $entity->type;
                    Version::create($versionData);
                }
            }

            return $entity->fresh(['authors', 'versions']);
        });
    }

    protected function validateMediaData(array $data): void
    {
        $validator = Validator::make($data, [
            'title' => 'required|string|max:255',
            'type' => 'sometimes|required|string|in:book,video,audio,manuscript',
            'file_path' => 'nullable|string',
            'author_ids' => 'nullable|array',
            'author_ids.*' => 'exists:authors,id',
            'publisher_id' => 'nullable|uuid|exists:publishers,id',
            'isbn' => 'nullable|string|max:20',
            'duration' => 'nullable|integer',
            'century' => 'nullable|string|max:100',
            'pages' => 'nullable|integer',
            'published_year' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }
}
