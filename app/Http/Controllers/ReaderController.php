<?php

namespace App\Http\Controllers;

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
    public function show(string $type, string $slug)
    {
        // 1. Try to resolve as a specific Content Node (Segment/Page/Chapter)
        try {
            $entity = $this->resolveEntity($type, $slug);
            $currentNodeSlug = $slug;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 2. If not found, try to resolve as a Parent Entity (Book/Audio/etc)
            $parentEntity = $this->resolveParentEntity($type, $slug);

            if ($parentEntity) {
                // Check for saved reading position
                if (auth()->check()) {
                    $position = $this->positionService->getPosition(auth()->user(), $parentEntity);
                    if ($position && $position->node_slug) {
                        return redirect()->route('reader.show', ['type' => $type, 'slug' => $position->node_slug]);
                    }
                }

                // Fallback: Find the first child node
                $firstChild = $this->contentService->getFirstChild($parentEntity);
                
                if ($firstChild) {
                    return redirect()->route('reader.show', ['type' => $type, 'slug' => $firstChild->slug]);
                }
            }
            // If still not found, throw 404
            abort(404, 'المحتوى غير موجود');
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
        if ($type === 'manuscript') {
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
            'content' => $data['contentNode']->json_content ?? ['type' => 'doc', 'content' => []],
            'html_content' => $data['contentNode']->content ?? '',
            'activeSlug' => $currentNodeSlug,
            'hierarchy' => $entity->children, // Already loaded by resolveEntity
            'readingPosition' => $savedPosition,
            'title' => $entity->title . ' | القارئ',
            'siblings_content' => $siblingsContent, // New prop for Vertical Scroll
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
        $entityModel = match ($type) {
            'book' => Book::class,
            'audio' => Audio::class,
            'video' => Video::class,
            'manuscript' => Manuscript::class,
            default => abort(404, "Unknown entity type")
        };

        $contentModel = $this->getContentModelClass($type);
        $node = $contentModel::where('slug', $slug)->firstOrFail();

        $foreignKey = match ($type) {
            'book' => 'book_id',
            'manuscript' => 'manuscript_id',
            'audio' => 'audio_id',
            'video' => 'video_id',
            default => 'entity_id'
        };

        $entity = $entityModel::findOrFail($node->$foreignKey);

        // Load hierarchy (children) based on type
        if (in_array($type, ['manuscript', 'audio', 'video'])) {
            $childrenModel = match ($type) {
                'manuscript' => ManuscriptPage::class,
                'audio' => AudioSegment::class,
                'video' => VideoSegment::class,
                default => null
            };

            if ($childrenModel) {
                $children = $childrenModel::where($foreignKey, $entity->id)
                    ->orderBy('order', 'asc')
                    ->get();
                $entity->setRelation('children', $children);
            }
        } elseif ($type === 'book') {
            $children = BookChild::where('book_id', $entity->id)
                ->orderBy('order', 'asc')
                ->get();
            $entity->setRelation('children', $children);
        } else {
            $entity->load('children');
        }

        return $entity;
    }

    /**
     * Helper to get content model class name
     */
    protected function getContentModelClass(string $type): string
    {
        return match ($type) {
            'book' => BookChild::class,
            'manuscript' => ManuscriptPage::class,
            'audio' => AudioSegment::class,
            'video' => VideoSegment::class,
            default => EntityContent::class,
        };
    }

    /**
     * Resolve Parent Entity directly by slug.
     */
    protected function resolveParentEntity(string $type, string $slug)
    {
        $modelClass = match ($type) {
            'book' => Book::class,
            'audio' => Audio::class,
            'video' => Video::class,
            'manuscript' => Manuscript::class,
            default => null
        };

        if (!$modelClass) return null;

        return $modelClass::where('slug', $slug)->first();
    }
}
