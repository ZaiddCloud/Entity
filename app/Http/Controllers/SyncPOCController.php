<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SyncPOCController extends Controller
{
    /**
     * Display the Sync POC demo page
     */
    public function index()
    {
        return Inertia::render('SyncPOC');
    }

    /**
     * API endpoint for entity retrieval
     * Supports If-Modified-Since for delta sync
     * 
     * Follows project architecture: Entity is polymorphic root,
     * we query concrete models (Book, Audio, Video, Manuscript)
     */
    public function getEntity(Request $request, string $type, $id)
    {
        // Resolve concrete model class based on type
        $modelClass = $this->resolveModelClass($type);
        $entity = $modelClass::findOrFail($id);

        // Check If-Modified-Since header for delta sync
        if ($request->hasHeader('If-Modified-Since')) {
            $ifModifiedSince = strtotime($request->header('If-Modified-Since'));
            $entityModified = strtotime($entity->updated_at);

            if ($entityModified <= $ifModifiedSince) {
                // Not modified - return 304
                return response()->json(null, 304);
            }
        }

        return response()->json([
            'entity' => [
                'id' => $entity->id,
                'title' => $entity->title,
                'type' => $type,
                'slug' => $entity->slug,
                'parent_id' => $entity->parent_id ?? null,
                'updated_at' => $entity->updated_at->toIso8601String(),
                'version_tag' => $entity->updated_at->timestamp,
            ],
            'sync_metadata' => [
                'server_time' => now()->toIso8601String(),
                'checksum' => md5($entity->toJson())
            ]
        ]);
    }

    /**
     * API endpoint for entity update
     * Supports version conflict detection
     */
    public function updateEntity(Request $request, string $type, $id)
    {
        $modelClass = $this->resolveModelClass($type);
        $entity = $modelClass::findOrFail($id);

        // Check for version conflicts
        // Check for version conflicts
        $strategy = $request->input('strategy', 'check');

        if ($strategy !== 'force' && $request->has('version_tag')) {
            $clientVersion = (int) $request->input('version_tag');
            $serverVersion = $entity->updated_at->timestamp;

            // Allow 2 second drift tolerance
            if ($clientVersion < ($serverVersion - 2)) {
                // Conflict detected
                return response()->json([
                    'conflict' => true,
                    'server_version' => [
                        'id' => $entity->id,
                        'title' => $entity->title,
                        'type' => $type,
                        'updated_at' => $entity->updated_at->toIso8601String(),
                        'version_tag' => $entity->updated_at->timestamp,
                        // Add other fields as needed for diff
                    ],
                    'client_version' => $request->all(),
                    'message' => 'Version conflict - server has newer data'
                ], 409);
            }
        }

        // Update entity
        $entity->update([
            'title' => $request->input('title', $entity->title),
        ]);

        return response()->json([
            'success' => true,
            'entity' => [
                'id' => $entity->id,
                'title' => $entity->title,
                'type' => $type,
                'updated_at' => $entity->updated_at->toIso8601String(),
                'version_tag' => $entity->updated_at->timestamp,
            ]
        ]);
    }

    /**
     * API endpoint to get a random entity ID for testing
     */
    public function getRandom(string $type)
    {
        $modelClass = $this->resolveModelClass($type);
        $entity = $modelClass::inRandomOrder()->first();

        if (!$entity) {
            return response()->json(['error' => 'No entities found'], 404);
        }

        return response()->json(['id' => $entity->id]);
    }

    /**
     * Resolve model class from type string
     * Following EntityQueryService pattern
     */
    private function resolveModelClass(string $type): string
    {
        return match (strtolower($type)) {
            'book' => Book::class,
            'audio' => Audio::class,
            'video' => Video::class,
            'manuscript' => Manuscript::class,
            default => throw new \InvalidArgumentException("Unknown entity type: {$type}")
        };
    }
}
