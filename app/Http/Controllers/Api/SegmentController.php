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

        // Calculate correct order based on chronological position
        $startTime = $request->start_time ?? 0;

        // Get all existing segments to find the correct position
        $modelClass = match ($request->entity_type) {
            'audio' => \App\Models\AudioSegment::class,
            'video' => \App\Models\VideoSegment::class,
        };

        $foreignKey = match ($request->entity_type) {
            'audio' => 'audio_id',
            'video' => 'video_id',
        };

        $existingSegments = $modelClass::where($foreignKey, $entity->id)
            ->orderBy('start_time', 'asc')
            ->get(['start_time', 'order']);

        // Find the position where this segment should be inserted
        $newOrder = 1;
        foreach ($existingSegments as $index => $seg) {
            if ($startTime < ($seg->start_time ?? 0)) {
                $newOrder = $index + 1;
                break;
            }
            $newOrder = $index + 2; // After this segment
        }

        // Shift all segments after this position
        $modelClass::where($foreignKey, $entity->id)
            ->where('order', '>=', $newOrder)
            ->increment('order');

        // Create the segment
        $segment = $this->contentService->createNode($entity, [
            'type' => $type,
            'title' => $request->title,
            'slug' => \App\Helpers\SlugHelper::generate($request->title) . '-' . Str::random(8),
            'content' => '<p></p>', // Empty content initially
            'start_time' => $startTime,
            'end_time' => $request->end_time ?? 0,
            'file_path' => $request->file_path,
            'order' => $newOrder,
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
            'title' => 'nullable|string|max:255',
            'start_time' => 'nullable|numeric|min:0',
        ]);

        $modelClass = match ($request->entity_type) {
            'audio' => \App\Models\Audio::class,
            'video' => \App\Models\Video::class,
        };

        $entity = $modelClass::findOrFail($request->entity_id);

        $segment = $this->contentService->getNode($entity, $id);

        // Handle case where ID might be passed as slug or ID
        if (!$segment) {
            return response()->json(['error' => 'Segment not found'], 404);
        }

        $updateData = [
            'last_updated' => now()
        ];

        // Update title if provided
        if ($request->has('title')) {
            $updateData['title'] = $request->title;
        }

        // Handle start_time change with re-ordering
        if ($request->has('start_time')) {
            $newStartTime = $request->start_time;
            $oldStartTime = $segment->start_time ?? 0;

            // If time changed, we need to re-order
            if (abs($newStartTime - $oldStartTime) > 0.1) {
                $segmentModelClass = match ($request->entity_type) {
                    'audio' => \App\Models\AudioSegment::class,
                    'video' => \App\Models\VideoSegment::class,
                };

                $foreignKey = match ($request->entity_type) {
                    'audio' => 'audio_id',
                    'video' => 'video_id',
                };

                // Get all segments except the current one
                $otherSegments = $segmentModelClass::where($foreignKey, $entity->id)
                    ->where('_id', '!=', $segment->_id)
                    ->orderBy('start_time', 'asc')
                    ->get(['start_time', 'order', '_id']);

                // Find new position
                $newOrder = 1;
                foreach ($otherSegments as $index => $seg) {
                    if ($newStartTime < ($seg->start_time ?? 0)) {
                        $newOrder = $index + 1;
                        break;
                    }
                    $newOrder = $index + 2;
                }

                // Remove old position (decrement all after old position)
                $segmentModelClass::where($foreignKey, $entity->id)
                    ->where('order', '>', $segment->order)
                    ->decrement('order');

                // Make space at new position
                $segmentModelClass::where($foreignKey, $entity->id)
                    ->where('order', '>=', $newOrder)
                    ->increment('order');

                $updateData['start_time'] = $newStartTime;
                $updateData['order'] = $newOrder;
            }
        }

        $segment->update($updateData);

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
