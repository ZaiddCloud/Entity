<?php

namespace App\Http\Controllers;

use App\Enums\EntityType;
use App\Models\Book;
use App\Models\Audio;
use App\Models\Video;
use App\Models\Manuscript;
use App\Models\Entity;
use App\Models\BookChild;
use App\Models\ManuscriptPage;
use App\Models\AudioSegment;
use App\Models\VideoSegment;
use App\Models\EntityContent;
use App\Services\EntityContentService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Gate;

class UnifiedEditorController extends Controller
{
    protected $contentService;

    public function __construct(EntityContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * المسار الموحد للمحرر: /studio/{type}/{slug}/{childId?}
     * slug is PARENT slug. childId is specific node ID.
     */
    public function show(string $type, string $slug, ?string $childId = null)
    {
        $entityType = EntityType::tryFrom($type);
        if (!$entityType)
            abort(404, 'Invalid entity type');

        // 1. Resolve Parent Entity
        $parentEntity = $this->resolveParentEntity($entityType, $slug);

        if (!$parentEntity) {
            abort(404, 'Parent resource not found');
        }

        // 2. Load Content
        // Always aggregate full content for instant client-side transitions
        $fullContent = $this->contentService->aggregateFullContent($parentEntity);
        $isFullView = false;

        if ($childId) {
            $modelClass = $this->getContentModelClass($entityType);
            $node = $modelClass::find($childId);

            // Validate child belongs to parent
            $foreignKey = $this->getForeignKey($entityType);
            if (!$node || $node->$foreignKey != $parentEntity->id) {
                // If ID is actually a slug (legacy or mistake), try finding by slug
                $node = $modelClass::where('slug', $childId)
                    ->where($foreignKey, $parentEntity->id)
                    ->first();
            }

            if (!$node) {
                abort(404, 'Specific content node not found');
            }

            $currentEditorContent = $node->content ?? '';
        } else {
            // Default: Load FULL CONTENT
            $currentEditorContent = $fullContent;
            $isFullView = true;

            // For UI state, we still might need a "reference" node if it's manuscript
            $node = $this->contentService->getFirstChild($parentEntity);
        }

        $entity = $parentEntity;

        // التحقق من الصلاحية
        Gate::authorize('update', $entity);

        // --- Hybrid Data Loading (MongoDB Children) ---
        // Since we removed HybridRelations from Entity base, we load children manually 
        // to ensure parity across all studio views without breaking SQL queries.
        if (in_array($entityType, [EntityType::MANUSCRIPT, EntityType::AUDIO, EntityType::VIDEO])) {
            $childrenModel = match ($entityType) {
                EntityType::MANUSCRIPT => ManuscriptPage::class,
                EntityType::AUDIO => AudioSegment::class,
                EntityType::VIDEO => VideoSegment::class,
                default => null
            };

            if ($childrenModel) {
                $foreignKey = $this->getForeignKey($entityType);
                $children = $childrenModel::where($foreignKey, $entity->id)
                    ->orderBy('order', 'asc')
                    ->get();

                $entity->setRelation('children', $children);
            }
        }

        // Load siblings for Manuscript if 'code' exists
        if ($entityType === EntityType::MANUSCRIPT && $entity->code) {
            $siblings = Manuscript::where('code', $entity->code)
                ->where('id', '!=', $entity->id)
                ->get();
            $entity->setRelation('siblings', $siblings);
        }

        // Record last active session
        if (auth()->check()) {
            auth()->user()->update([
                'last_studio_type' => $type, // DB stores string
                'last_studio_slug' => $slug,
                'last_studio_child_id' => $childId ? ($node->_id ?? $node->id) : null
            ]);
        }

        $data = $this->contentService->prepareEditorData($entity, $node?->slug);

        // CRITICAL FIX: Ensure contentNode is explicitly set for StudioLayout
        // When we have a specific node selected, pass it directly
        if ($node && !$isFullView) {
            $data['contentNode'] = $node;
        }

        // Map to Studio Props
        $studioProps = [
            'type' => $type, // Frontend expects string currently
            'entity' => $entity, // Entity now includes siblings and children if loaded
            'editorContent' => $currentEditorContent,
            'fullContent' => $fullContent,
            'isFullView' => $isFullView,
            'activeChildId' => $isFullView ? null : ($node->_id ?? $node->id),
            'title' => $entity->title . ' | Entity Studio',
            // Pass legacy data if needed by EditorClient internally via provide/inject or initial config
            '_legacy' => $data
        ];

        return Inertia::render('Technologies/Studio/StudioLayout', $studioProps);
    }
    // Added missing brace

    /**
     * حفظ المحتوى: /editor/{type}/{slug}/save
     */
    /**
     * حفظ المحتوى: /editor/{type}/{slug}/save
     */
    public function save(Request $request, string $type, string $slug, ?string $childId = null)
    {
        $request->validate([
            'content' => 'required', // Can be string (legacy) or payload array
            'html_content' => 'nullable|string',
            'json_content' => 'nullable|array',
            'plain_text' => 'nullable|string',
            'child_id' => 'nullable|string', // Support explicit ID in payload
            'title' => 'nullable|string|max:255', // Add title validation
        ]);

        $childToSave = $childId ?: $request->input('child_id');

        $entityType = EntityType::tryFrom($type);
        if (!$entityType)
            abort(404, 'Invalid entity type');

        // Authorize via parent
        $parent = $this->resolveParentEntity($entityType, $slug);
        if (!$parent)
            abort(404, 'Parent entity not found');
        Gate::authorize('update', $parent);

        // --- HANDLE SMART SAVE (FULL VIEW) ---
        if ($childToSave === 'full') {
            return $this->handleFullViewSave($request, $entityType, $parent);
        }

        if (!$childToSave) {
            return response()->json(['error' => 'Child ID is required for saving'], 422);
        }

        // --- RESOLVE SPECIFIC NODE ---
        $modelClass = $this->getContentModelClass($entityType);
        $node = $modelClass::find($childToSave);

        // Fallback for slug if needed
        if (!$node) {
            $foreignKey = $this->getForeignKey($entityType);
            $node = $modelClass::where('slug', $childToSave)
                ->where($foreignKey, $parent->id)
                ->first();
        }

        if (!$node) {
            return response()->json(['error' => 'Specific content node not found'], 404);
        }

        // Prepare Payload
        $updateData = [
            'last_updated' => now(),
            'last_editor_id' => $request->user()->id
        ];

        // Handle Title Update
        if ($request->has('title')) {
            $updateData['title'] = $request->input('title');
        }

        // Handle content formats
        if (is_array($request->input('content'))) {
            // New Format direct payload
            $payload = $request->input('content');
            $updateData['content'] = $payload['html'] ?? '';
            $updateData['json_content'] = $payload['json'] ?? [];
            $updateData['plain_text'] = $payload['text'] ?? '';
        } else {
            // Legacy fallback or explicit fields
            $updateData['content'] = $request->input('html_content') ?? $request->input('content');
            if ($request->has('json_content'))
                $updateData['json_content'] = $request->input('json_content');
            if ($request->has('plain_text'))
                $updateData['plain_text'] = $request->input('plain_text');
        }

        $node->update($updateData);

        return response()->json([
            'message' => 'تم الحفظ بنجاح',
            'last_saved' => now()->toIso8601String()
        ]);
    }

    /**
     * معالجة الحفظ الذكي لوضع "كامل المحتوى"
     * يقوم بتقسيم النص المجمع وإمالة كل جزء لمقطعه الأصلي
     */
    protected function handleFullViewSave(Request $request, EntityType $type, $parent)
    {
        $html = null;
        if (is_array($request->input('content'))) {
            $html = $request->input('content')['html'] ?? '';
        } else {
            $html = $request->input('html_content') ?? $request->input('content');
        }

        if (empty($html)) {
            return response()->json(['error' => 'Content is required'], 422);
        }

        $modelClass = $this->getContentModelClass($type);
        $foreignKey = $this->getForeignKey($type);

        $children = $modelClass::where($foreignKey, $parent->id)
            ->orderBy('order')
            ->get();

        if ($children->isEmpty()) {
            return response()->json(['message' => 'No segments found to update'], 200);
        }

        // --- PREPARE UPDATES ---
        $segmentsData = $request->input('segments');
        $parts = preg_split('/<p><strong>.*?:<\/strong><\/p>/', $html, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($children as $index => $child) {
            $updateData = [
                'last_updated' => now(),
                'last_editor_id' => $request->user()->id
            ];

            // Update HTML from parts (already split on backend)
            if (isset($parts[$index])) {
                $content = trim($parts[$index]);
                $content = preg_replace('/^<p><br\/><\/p>/', '', $content);
                $content = preg_replace('/<p><br\/><\/p>$/', '', $content);

                $updateData['content'] = $content;
                $updateData['plain_text'] = strip_tags($content);
            }

            // Update JSON from frontend segments if available
            if ($segmentsData && isset($segmentsData[$index]['json'])) {
                $updateData['json_content'] = $segmentsData[$index]['json'];
            } else {
                // If no JSON provided, reset it so it regenerates from HTML on load
                $updateData['json_content'] = null;
            }

            $child->update($updateData);
        }

        return response()->json([
            'message' => 'تم تحديث كافة المقاطع بنجاح (حفظ ذكي)',
            'last_saved' => now()->toIso8601String()
        ]);
    }

    /**
     * استئناف العمل بمجرد الدخول: /resume
     */
    public function resume(Request $request)
    {
        $user = $request->user();

        if ($user && $user->last_studio_type && $user->last_studio_slug) {
            return redirect()->route('studio.show', [
                'type' => $user->last_studio_type,
                'slug' => $user->last_studio_slug,
                'childId' => $user->last_studio_child_id
            ]);
        }

        // Fallback: Check if we have any recently updated content
        $book = Book::first();

        if ($book) {
            return redirect()->route('studio.show', ['type' => EntityType::BOOK->value, 'slug' => $book->slug]);
        }

        return redirect()->route('dashboard');
    }

    protected function getForeignKey(EntityType $type): string
    {
        return match ($type) {
            EntityType::BOOK => 'book_id',
            EntityType::MANUSCRIPT => 'manuscript_id',
            EntityType::AUDIO => 'audio_id',
            EntityType::VIDEO => 'video_id',
        };
    }


    /**
     * حل الكيان برمجياً بناءً على النوع
     */
    protected function resolveEntity(EntityType $type, string $slug): Entity
    {
        $entityModel = $type->modelClass();

        $contentModel = $this->getContentModelClass($type);
        $node = $contentModel::where('slug', $slug)->firstOrFail();

        // Determine foreign key based on type
        $foreignKey = $this->getForeignKey($type);

        $entity = $entityModel::findOrFail($node->$foreignKey);

        // Manually load children for Mongo-Hybrid relations if needed
        // Since we removed HybridRelations from Entity, standard eager loading fails for Mongo children
        if (in_array($type, [EntityType::MANUSCRIPT, EntityType::AUDIO, EntityType::VIDEO])) {
            $childrenModel = match ($type) {
                EntityType::MANUSCRIPT => ManuscriptPage::class,
                EntityType::AUDIO => AudioSegment::class,
                EntityType::VIDEO => VideoSegment::class,
                default => null
            };

            if ($childrenModel) {
                // Fetch children directly from Mongo
                $children = $childrenModel::where($foreignKey, $entity->id)
                    ->orderBy('order', 'asc')
                    ->get();

                $entity->setRelation('children', $children);
            }
        }
        // For Book (if BookChild is Mongo), we should also load manually or check if it works via standard relation
        elseif ($type === EntityType::BOOK) {
            $children = BookChild::where('book_id', $entity->id)
                ->orderBy('order', 'asc')
                ->get();
            $entity->setRelation('children', $children);
        } else {
            // Fallback for standard SQL-SQL
            $entity->load('children');
        }

        return $entity;
    }

    /**
     * Helper to get model class name
     */
    protected function getContentModelClass(EntityType $type): string
    {
        return match ($type) {
            EntityType::BOOK => BookChild::class,
            EntityType::MANUSCRIPT => ManuscriptPage::class,
            EntityType::AUDIO => AudioSegment::class,
            EntityType::VIDEO => VideoSegment::class,
        };
    }
    /**
     * Resolve Parent Entity directly
     */
    protected function resolveParentEntity(EntityType $type, string $slug)
    {
        $modelClass = $type->modelClass();
        return $modelClass::where('slug', $slug)->first();
    }
}
