<?php

namespace App\Http\Controllers;

use App\Enums\EntityType;
use App\Enums\ContentNodeType;
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

        if ($childId && $childId !== 'full') {
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

            if (in_array($entityType, [EntityType::BOOK, EntityType::MANUSCRIPT])) {
                $currentEditorContent = $this->contentService->getBranchContent($parentEntity, $childId);
            } else {
                $currentEditorContent = $node->content ?? '';
            }
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
                $query = $childrenModel::where($foreignKey, $entity->id);
                if (in_array($entityType, [EntityType::AUDIO, EntityType::VIDEO])) {
                    $query->orderBy('start_time', 'asc')->orderBy('order', 'asc');
                } else {
                    $query->orderBy('order', 'asc');
                }
                $children = $query->get();

                $entity->setRelation('children', $children);
            }
        }

        // Load siblings for Manuscript if 'code' exists (View all copies with the same work code prefix)
        if ($entityType === EntityType::MANUSCRIPT && $entity->code) {
            // Extract the prefix (e.g., 'ج-ش-م-م-م-ك-0074' -> 'ج-ش-م-م-م-ك')
            $parts = explode('-', $entity->code);
            array_pop($parts); // Remove the sequential number
            $workPrefix = implode('-', $parts);

            $siblings = Manuscript::where('code', 'LIKE', $workPrefix . '-%')
                ->where('id', '!=', $entity->id)
                ->get();

            // CRITICAL: Also load children (pages) for siblings so they are available in ManuscriptStore
            foreach ($siblings as $sibling) {
                $siblingChildren = ManuscriptPage::where('manuscript_id', $sibling->id)
                    ->orderBy('order', 'asc')
                    ->get();
                $sibling->setRelation('children', $siblingChildren);
            }

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
            'visual_map' => ContentNodeType::getVisualMap($entityType),
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

        $query = $modelClass::where($foreignKey, $parent->id);
        if (in_array($type, [EntityType::AUDIO, EntityType::VIDEO])) {
            $query->orderBy('start_time', 'asc')->orderBy('order', 'asc');
        } else {
            $query->orderBy('order', 'asc');
        }
        $children = $query->get();

        if ($children->isEmpty()) {
            return response()->json(['message' => 'No segments found to update'], 200);
        }

        // --- PREPARE UPDATES ---
        $segmentsData = $request->input('segments');
        $frontendDataMap = [];
        if (is_array($segmentsData)) {
            foreach ($segmentsData as $seg) {
                if (isset($seg['id'])) {
                    $frontendDataMap[(string) $seg['id']] = $seg;
                }
            }
        }

        // Robust Split & ID Mapping: Extract positions of all headers
        // Header Signature: <h4 class="structure-marker" data-segment-link="true" data-id="ID" ...>TITLE:</h4>
        $markerRegex = '/<h4[^>]*class="[^"]*structure-marker[^"]*"[^>]*>.*?<\/h4>/siu';
        \Illuminate\Support\Facades\Log::info('[UnifiedEditor@handleFullViewSave] Starting save', [
            'entity_id' => $parent->id,
            'html_length' => strlen($html),
            'headerCount_expected' => count($children)
        ]);
        preg_match_all($markerRegex, $html, $matches, PREG_OFFSET_CAPTURE);
        
        $htmlDataMap = [];
        $fullHtmlDataMap = []; // Initialize to store full HTML content for each segment
        $headerCount = count($matches[0]);
        
        for ($i = 0; $i < $headerCount; $i++) {
            $headerHtml = $matches[0][$i][0];
            $headerStart = $matches[0][$i][1];
            $headerEnd = $headerStart + strlen($headerHtml);
            
            // Extract ID and Title from the h4 marker
            preg_match('/data-id="(?P<id>[^"]+)"/i', $headerHtml, $idMatch);
            // Title is the content of the h4
            preg_match('/<h4[^>]*>(?P<title>.*?)<\/h4>/siu', $headerHtml, $titleMatch);
            
            $id = $idMatch['id'] ?? null;
            $title = $titleMatch['title'] ?? null;
            
            if (!$id) {
                \Illuminate\Support\Facades\Log::warning('[UnifiedEditor@handleFullViewSave] Header missing data-id', ['header' => $headerHtml]);
                continue;
            }

            // Content is between this header and the next header
            $nextHeaderStart = ($i + 1 < $headerCount) ? $matches[0][$i + 1][1] : strlen($html);
            $content = substr($html, $headerEnd, $nextHeaderStart - $headerEnd);
            
            // Clean content: remove leading/trailing breaks
            $content = trim($content);
            $content = preg_replace('/^<p><br\/><\/p>/', '', $content);
            $content = preg_replace('/<p><br\/><\/p>$/', '', $content);

            $htmlDataMap[(string) $id] = [
                'title' => trim($title, " :\t\n\r\0\x0B"),
                'header_found' => true,
                'content_length' => strlen($content)
            ];

            // Real content save
            $fullHtmlDataMap[(string) $id] = $content;
        }

        \Illuminate\Support\Facades\Log::info('[UnifiedEditor@handleFullViewSave] Mapping results', [
            'headers_matched' => $headerCount,
            'ids_mapped' => array_keys($htmlDataMap)
        ]);

        // Fallback: If no headers found and only one child exists, assume entire HTML belongs to that child
        if ($headerCount === 0 && count($children) === 1) {
            $child = $children[0];
            $childId = (string) ($child->id ?? $child->_id);
            $fullHtmlDataMap[$childId] = $html;
            
            // Extract JSON content from the full document if available
            // Note: EditorStore sends this as json_content at the top level
            $fullDocJson = $request->input('json_content') ?? null;
            if (is_array($fullDocJson) && isset($fullDocJson['content'])) {
                $frontendDataMap[$childId]['json'] = $fullDocJson['content'];
                \Illuminate\Support\Facades\Log::debug('[UnifiedEditor@handleFullViewSave] JSON extracted for fallback', [
                    'nodeCount' => count($fullDocJson['content'])
                ]);
            } else {
                \Illuminate\Support\Facades\Log::warning('[UnifiedEditor@handleFullViewSave] JSON content missing in full document', [
                    'has_json' => isset($fullDocJson),
                    'has_content' => isset($fullDocJson['content'])
                ]);
            }
            
            \Illuminate\Support\Facades\Log::info('[UnifiedEditor@handleFullViewSave] Applied single-child fallback', ['childId' => $childId]);
        }

        foreach ($children as $child) {
            $childId = (string) ($child->id ?? $child->_id);
            
            $updateData = [
                'last_updated' => now(),
                'last_editor_id' => $request->user()->id
            ];

            // 1. Sync Title (Prefer frontend structured data, fallback to HTML header)
            if (isset($frontendDataMap[$childId]['title'])) {
                $updateData['title'] = $frontendDataMap[$childId]['title'];
            } elseif (isset($htmlDataMap[$childId]['title'])) {
                $updateData['title'] = $htmlDataMap[$childId]['title'];
            }

            // 2. Sync Content (Prefer HTML extraction because splitting is accurate now)
            if (isset($fullHtmlDataMap[$childId])) {
                $contentToSave = $fullHtmlDataMap[$childId];
                $updateData['content'] = $contentToSave;
                $updateData['plain_text'] = strip_tags($contentToSave);
            }

            // 3. Sync JSON (Only if provided by frontend segments)
            if (isset($frontendDataMap[$childId]['json'])) {
                $updateData['json_content'] = $frontendDataMap[$childId]['json'];
                \Illuminate\Support\Facades\Log::debug('[UnifiedEditor@handleFullViewSave] Setting JSON for child', [
                    'childId' => $childId,
                    'json_type' => gettype($updateData['json_content'])
                ]);
            } else {
                // If no JSON was matched, reset it so it re-renders from HTML
                $updateData['json_content'] = null;
            }

            if (empty($updateData['content']) && empty($updateData['title'])) {
                \Illuminate\Support\Facades\Log::warning('[UnifiedEditor@handleFullViewSave] No updates found for child', ['childId' => $childId]);
            }

            \Illuminate\Support\Facades\Log::debug('[UnifiedEditor@handleFullViewSave] Updating child', [
                'childId' => $childId,
                'title' => $updateData['title'] ?? 'N/A',
                'content_length' => isset($updateData['content']) ? strlen($updateData['content']) : 0
            ]);

            $child->update($updateData);
        }

        return response()->json([
            'message' => 'تم الحفظ بنجاح', // Ensure exact match for test
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
            $query = BookChild::where('book_id', $entity->id)
                ->orderBy('order', 'asc');
            $children = $query->get();
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
