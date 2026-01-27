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
use App\Services\ReadingPositionService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Log;

class ReaderController extends Controller
{
    protected $contentService;
    protected $positionService;

    public function __construct(EntityContentService $contentService, ReadingPositionService $positionService)
    {
        $this->contentService = $contentService;
        $this->positionService = $positionService;
    }

    /**
     * Display the Reader for a specific entity/content node.
     * Path: /reader/{type}/{slug}
     */
    public function show(string $type, string $slug, string $childId = null)
    {
        // 1. Resolve Parent Entity
        $parentEntity = $this->resolveParentEntity($type, $slug);

        if (!$parentEntity) {
            // fallback: check if slug belongs to a child (legacy support or direct node link)
            $childNode = $this->resolveEntityNode($type, $slug);
            if ($childNode) {
                 // Redirect to canonical parent-based URL
                 $foreignKey = $this->getForeignKey($type);
                 $parent = $childNode->getRelationValue(str_replace('_id', '', $foreignKey)) ?: $this->resolveParentModel($type)::find($childNode->$foreignKey);
                 return redirect()->route('reader.show', ['type' => $type, 'slug' => $parent->slug, 'childId' => $childNode->_id ?? $childNode->id]);
            }
            abort(404, 'المصدر غير موجود');
        }

        $entity = $parentEntity;
        $entity->load(['authors', 'categories', 'tags']);

        // 2. Resolve Content
        $node = null;
        $htmlContent = '';
        $jsonContent = null;
        $isFullView = false;
        $currentNodeSlug = null;

        if ($childId) {
            $modelClass = $this->getContentModelClass($type);
            $node = $modelClass::find($childId);

            // Validate child belongs to parent
            $foreignKey = $this->getForeignKey($type);
            if (!$node || $node->$foreignKey != $entity->id) {
                // Try finding by slug if ID failed
                $node = $modelClass::where('slug', $childId)
                    ->where($foreignKey, $entity->id)
                    ->first();
            }

            if (!$node) {
                abort(404, 'المقطع المحدد غير موجود');
            }

            $htmlContent = $node->content ?? '';
            $jsonContent = $node->json_content ?? ['type' => 'doc', 'content' => []];
            $currentNodeSlug = $node->slug;
        } else {
            // FULL VIEW
            $htmlContent = $this->contentService->aggregateFullContent($entity);
            $isFullView = true;
            
            // For metadata/hierarchy we still point at first child or null
            $node = $this->contentService->getFirstChild($entity);
        }

        // 3. Load Additional Metadata
        $entity->load(['authors', 'categories', 'tags']);

        // 4. Prepare Content Data
        $data = $this->contentService->prepareEditorData($entity, $currentNodeSlug);
        
        // 5. Get Reading Position for the User
        $savedPosition = null;
        if (auth()->check()) {
            $savedPosition = $this->positionService->getPosition(auth()->user(), $entity);
        }

        // 6. Special Handling for Manuscripts (Vertical Scroll)
        $siblingsContent = [];
        if (EntityType::tryFrom($type) === EntityType::MANUSCRIPT) {
            $siblingsContent = $entity->children->map(function($child) use ($type) {
                // Determine content node for each child
                $childNode = $child; 
                // Logic usually handled in prepareEditorData, simplified here for read-only View
                // If the child HAS content, use it.
                return [
                    'id' => $child->id,
                    'slug' => $child->slug,
                    'title' => $child->title,
                    'content' => $child->json_content ?? ['type' => 'doc', 'content' => []],
                    'html_content' => $child->content ?? '',
                    'metadata' => $child->metadata ?? [],
                ];
            });
        }

        return Inertia::render('Technologies/Reader/ReaderClient', [
            'type' => $type,
            'entity' => $entity,
            'content' => $jsonContent,
            'html_content' => $htmlContent,
            'isFullView' => $isFullView,
            'activeChildId' => $isFullView ? null : ($node->_id ?? $node->id),
            'activeSlug' => $currentNodeSlug,
            'hierarchy' => $entity->children, 
            'readingPosition' => $savedPosition,
            'title' => $entity->title . ' | القارئ',
            'siblings_content' => $siblingsContent, 
        ]);
    }

    /**
     * Save reading position (API endpoint)
     */
    public function savePosition(Request $request)
    {
        $request->validate([
            'entity_id' => 'required',
            'entity_type' => 'required',
            'node_slug' => 'required|string',
            'scroll_offset' => 'nullable|integer',
            'timestamp' => 'nullable|integer',
        ]);

        $user = auth()->user();
        $entityClass = \Illuminate\Database\Eloquent\Relations\Relation::getMorphedModel($request->entity_type) ?? $request->entity_type;
        $entity = $entityClass::findOrFail($request->entity_id);

        $this->positionService->savePosition($user, $entity, $request->only(['node_slug', 'scroll_offset', 'timestamp']));

        return response()->json(['message' => 'تم حفظ موضع القراءة']);
    }

    /**
     * Search across all content nodes for a specific entity.
     */
    public function search(Request $request, string $type, string $slug)
    {
        $query = $request->input('q');
        if (empty($query)) {
            return response()->json(['results' => []]);
        }

        // 1. Resolve Parent Entity
        $entity = $this->resolveParentEntity($type, $slug);
        
        // If slug was a child, resolve from child
        if (!$entity) {
             $entity = $this->resolveEntity($type, $slug);
        }

        // 2. Query Content Nodes
        $contentModel = $this->getContentModelClass($type);
        $foreignKey = match ($type) {
            'book' => 'book_id',
            'manuscript' => 'manuscript_id',
            'audio' => 'audio_id',
            'video' => 'video_id',
            default => 'entity_id'
        };

        // Perform text search (using simple like for now, or full-text if supported)
        $results = $contentModel::where($foreignKey, $entity->id)
            ->where(function($q) use ($query) {
                $q->where('plain_text', 'LIKE', "%{$query}%")
                  ->orWhere('title', 'LIKE', "%{$query}%");
            })
            ->orderBy('order', 'asc')
            ->get(['id', 'slug', 'title', 'plain_text', 'start_time']); // Optimize select

        // 3. Format results with snippets
        $formattedResults = $results->map(function($node) use ($query) {
            $snippet = '';
            if ($node->plain_text) {
                $pos = mb_stripos($node->plain_text, $query);
                $start = max(0, $pos - 40);
                $length = mb_strlen($query) + 80;
                $snippet = mb_substr($node->plain_text, $start, $length);
                if ($start > 0) $snippet = '...' . $snippet;
                if (mb_strlen($node->plain_text) > $start + $length) $snippet .= '...';
            }

            return [
                'id' => $node->id,
                'slug' => $node->slug,
                'title' => $node->title,
                'snippet' => $snippet,
                'timestamp' => $node->start_time ?? null,
            ];
        });

        return response()->json([
            'results' => $formattedResults,
            'count' => $formattedResults->count(),
            'query' => $query
        ]);
    }

    /**
     * Resolve the parent entity based on a content node's slug.
     */
    protected function resolveEntity(string $type, string $slug): Entity
    {
        $entityType = EntityType::tryFrom($type);
        if (!$entityType) abort(404, "Unknown entity type");

        $entityModel = $entityType->modelClass();

        $contentModel = $this->getContentModelClass($entityType);
        $node = $contentModel::where('slug', $slug)->firstOrFail();

        $foreignKey = $this->getForeignKey($entityType);

        $entity = $entityModel::findOrFail($node->$foreignKey);

        // Load hierarchy (children) based on type
        if (in_array($entityType, [EntityType::MANUSCRIPT, EntityType::AUDIO, EntityType::VIDEO])) {
            $childrenModel = match ($entityType) {
                EntityType::MANUSCRIPT => ManuscriptPage::class,
                EntityType::AUDIO => AudioSegment::class,
                EntityType::VIDEO => VideoSegment::class,
                default => null
            };

            if ($childrenModel) {
                $children = $childrenModel::where($foreignKey, $entity->id)
                    ->orderBy('order', 'asc')
                    ->get();
                $entity->setRelation('children', $children);
            }
        } elseif ($entityType === EntityType::BOOK) {
            $children = BookChild::where('book_id', $entity->id)
                ->orderBy('order', 'asc')
                ->get();
            $entity->setRelation('children', $children);
        } else {
            $entity->load('children');
        }

        return $entity;
    }

    protected function resolveEntityNode(EntityType $type, string $slug)
    {
        $contentModel = $this->getContentModelClass($type);
        return $contentModel::where('slug', $slug)->first();
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

    protected function resolveParentModel(EntityType $type): string
    {
        return $type->modelClass();
    }

    /**
     * Helper to get content model class name
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
     * Resolve Parent Entity directly by slug.
     */
    protected function resolveParentEntity(string $type, string $slug)
    {
        $entityType = EntityType::tryFrom($type);
        if (!$entityType) return null;

        $modelClass = $entityType->modelClass();
        return $modelClass::where('slug', $slug)->first();
    }
}
