<?php

namespace App\Http\Controllers\Api;

use App\Enums\EntityType;
use App\Enums\ContentNodeType;
use App\Http\Controllers\Controller;
use App\Services\EntityContentService;
use App\Models\AudioSegment;
use App\Models\VideoSegment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SegmentController extends Controller
{
    protected $contentService;

    public function __construct(EntityContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Store a new segment/scene for audio/video
     */
    public function store(Request $request)
    {
        $request->validate([
            'entity_id' => 'required|string',
            'entity_type' => 'required|in:audio,video',
            'title' => 'required|string|max:255',
            'start_time' => 'nullable|numeric|min:0',
            'end_time' => 'nullable|numeric|min:0',
            'file_path' => 'nullable|string',
        ]);

        // Get the entity
        $modelClass = match ($request->entity_type) {
            'audio' => \App\Models\Audio::class,
            'video' => \App\Models\Video::class,
        };

        $entity = $modelClass::findOrFail($request->entity_id);

        // Determine segment type
        $entityType = EntityType::from($request->entity_type);
        $type = ContentNodeType::defaultFor($entityType)->value;

        // Get next order number
        $maxOrder = $this->contentService->getMaxOrder($entity);

        // Create the segment
        $segment = $this->contentService->createNode($entity, [
            'type' => $type,
            'title' => $request->title,
            'slug' => \App\Helpers\SlugHelper::generate($request->title) . '-' . Str::random(8),
            'content' => '<p></p>', // Empty content initially
            'start_time' => $request->start_time ?? 0,
            'end_time' => $request->end_time ?? 0,
            'file_path' => $request->file_path,
            'order' => $maxOrder + 1,
        ]);

        return response()->json([
            'message' => 'تم إنشاء المقطع بنجاح',
            'segment' => $segment
        ], 201);
    }

    /**
     * Update a segment
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'entity_id' => 'required|string',
            'entity_type' => 'required|in:audio,video',
            'title' => 'required|string|max:255',
        ]);

        $modelClass = match ($request->entity_type) {
            'audio' => \App\Models\Audio::class,
            'video' => \App\Models\Video::class,
        };

        $entity = $modelClass::findOrFail($request->entity_id);

        $segment = $this->contentService->getNode($entity, $id);
        
        // Handle case where ID might be passed as slug or ID
        if (!$segment) {
            // Service usually handles resolving by ID/Slug, but let's be safe
             return response()->json(['error' => 'Segment not found'], 404);
        }

        $segment->update([
            'title' => $request->title,
            'last_updated' => now()
        ]);

        return response()->json([
            'message' => 'تم تحديث المقطع بنجاح',
            'segment' => $segment
        ]);
    }

    /**
     * Delete a segment
     */
    public function destroy(Request $request, string $id)
    {
        $request->validate([
            'entity_id' => 'required|string',
            'entity_type' => 'required|in:audio,video',
        ]);

        $modelClass = match ($request->entity_type) {
            'audio' => \App\Models\Audio::class,
            'video' => \App\Models\Video::class,
        };

        $entity = $modelClass::findOrFail($request->entity_id);

        $this->contentService->deleteNode($entity, $id);

        return response()->json([
            'message' => 'تم حذف المقطع بنجاح'
        ]);
    }
}
