<?php

namespace App\Http\Controllers;

use App\Enums\EntityType;
use App\Services\EntityContentService;
use Illuminate\Http\Request;

/**
 * Step 5: ContentNodeController 🎮
 * 
 * Handles persistence for new content nodes added via Studio.
 */
class ContentNodeController extends Controller
{
    protected $contentService;

    public function __construct(EntityContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Store a newly created node.
     */
    public function store(Request $request, string $type, string $slug)
    {
        $request->validate([
            'type' => 'required|string',
            'title' => 'required|string',
            'time' => 'nullable|numeric',
            'parent_id' => 'nullable|string'
        ]);

        $entityType = EntityType::tryFrom($type);
        if (!$entityType) abort(404, 'Invalid entity type');

        $modelClass = $entityType->modelClass();
        $entity = $modelClass::where('slug', $slug)->first();

        if (!$entity) abort(404, 'Parent entity not found');

        // Step 13: Media Duration Validation ⏳
        if ($request->filled('time') && ($entityType === EntityType::AUDIO || $entityType === EntityType::VIDEO)) {
            $duration = $entity->duration ?? 0;
            if ($request->input('time') > $duration) {
                return response()->json([
                    'message' => 'تعذر الإضافة: الوقت المدخل يتجاوز مدة ملف الميديا (' . $duration . ' ثانية)',
                    'errors' => ['time' => ['الوقت المدخل يتجاوز مدة الملف']]
                ], 422);
            }
        }

        $node = $this->contentService->addNode(
            $entity,
            $request->input('type'),
            $request->input('title'),
            $request->input('time'),
            $request->input('parent_id')
        );

        return response()->json([
            'message' => 'تمت إضافة العنصر بنجاح',
            'node' => $node,
            'redirect' => route('studio.show', [
                'type' => $type,
                'slug' => $slug,
                'childId' => $node->_id ?? $node->id
            ])
        ]);
    }
}
