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
        // 1. Resolve Parent Entity
        $parentEntity = $this->resolveParentEntity($type, $slug);
        
        if (!$parentEntity) {
            abort(404, 'Parent resource not found');
        }

        // 2. Load Content
        $node = null;
        $editorContent = '';
        $isFullView = false;

        if ($childId) {
            $modelClass = $this->getContentModelClass($type);
            $node = $modelClass::find($childId);
            
            // Validate child belongs to parent
            $foreignKey = $this->getForeignKey($type);
            if (!$node || $node->$foreignKey != $parentEntity->id) {
                // If ID is actually a slug (legacy or mistake), try finding by slug
                $node = $modelClass::where('slug', $childId)
                    ->where($foreignKey, $parentEntity->id)
                    ->first();
            }

            if (!$node) {
                abort(404, 'Specific content node not found');
            }

            $editorContent = $node->content ?? '';
        } else {
            // Default: Load FULL CONTENT
            $editorContent = $this->contentService->aggregateFullContent($parentEntity);
            $isFullView = true;
            
            // For UI state, we still might need a "reference" node if it's manuscript
            $node = $this->contentService->getFirstChild($parentEntity);
        }

        $entity = $parentEntity;



        // التحقق من الصلاحية
        Gate::authorize('update', $entity);

        // Load siblings for Manuscript if 'code' exists
        if (EntityType::tryFrom($type) === EntityType::MANUSCRIPT && $entity->code) {
             $siblings = Manuscript::where('code', $entity->code)
                ->where('id', '!=', $entity->id)
                ->get();
             $entity->setRelation('siblings', $siblings);
        }

        // Record last active session
        if (auth()->check()) {
            auth()->user()->update([
                'last_studio_type' => $type,
                'last_studio_slug' => $slug,
                'last_studio_child_id' => $childId ? ($node->_id ?? $node->id) : null
            ]);
        }

        $data = $this->contentService->prepareEditorData($entity, $node->slug);

        
        // Map to Studio Props
        $studioProps = [
            'type' => $type,
            'entity' => $entity, // Entity now includes siblings if loaded
            'editorContent' => $editorContent,
            'isFullView' => $isFullView,
            'activeChildId' => $isFullView ? null : ($node->_id ?? $node->id),
            'title' => $entity->title . ' | Entity Studio',
            // Pass legacy data if needed by EditorClient internally via provide/inject or initial config
            '_legacy' => $data 
        ];

        return Inertia::render('Technologies/Studio/StudioLayout', $studioProps);
    } // Added missing brace

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
        ]);

        $childToSave = $childId ?: $request->input('child_id');

        if (!$childToSave) {
             return response()->json(['error' => 'Child ID is required for saving'], 422);
        }

        $modelClass = $this->getContentModelClass($type);
        $node = $modelClass::findOrFail($childToSave);

        // Authorize via parent
        $parent = $this->resolveParentEntity($type, $slug);
        Gate::authorize('update', $parent);


        // Prepare Payload
        $updateData = [
            'last_updated' => now(),
            'last_editor_id' => $request->user()->id
        ];

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
             if ($request->has('json_content')) $updateData['json_content'] = $request->input('json_content');
             if ($request->has('plain_text')) $updateData['plain_text'] = $request->input('plain_text');
        }

        $node->update($updateData);

        return response()->json([
            'message' => 'تم الحفظ بنجاح',
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
            return redirect()->route('studio.show', ['type' => 'book', 'slug' => $book->slug]);
        }

        return redirect()->route('dashboard');
    }

    protected function getForeignKey(string $type): string
    {
        return match ($type) {
            'book' => 'book_id',
            'manuscript' => 'manuscript_id',
            'audio' => 'audio_id',
            'video' => 'video_id',
            default => 'entity_id'
        };
    }


    /**
     * حل الكيان برمجياً بناءً على النوع
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

        // Determine foreign key based on type
        $foreignKey = match ($type) {
            'book' => 'book_id',
            'manuscript' => 'manuscript_id',
            'audio' => 'audio_id',
            'video' => 'video_id',
            default => 'entity_id'
        };

        $entity = $entityModel::findOrFail($node->$foreignKey);

        // Manually load children for Mongo-Hybrid relations if needed
        // Since we removed HybridRelations from Entity, standard eager loading fails for Mongo children
        if (in_array($type, ['manuscript', 'audio', 'video'])) {
            $childrenModel = match ($type) {
                'manuscript' => ManuscriptPage::class,
                'audio' => AudioSegment::class,
                'video' => VideoSegment::class,
                default => null
            };

            if ($childrenModel) {
                // Fetch children directly from Mongo
                $children = $childrenModel::where($foreignKey, $entity->id)
                    ->orderBy('order', 'asc')
                    ->get();
                
             $entity->setRelation('children', $children);
             \Illuminate\Support\Facades\Log::info("UnifiedEditorController: Manually loaded {$children->count()} children");
             \Illuminate\Support\Facades\Log::info("Serialized Entity Keys: " . implode(',', array_keys($entity->toArray())));
             if(isset($entity->toArray()['children'])) {
                 \Illuminate\Support\Facades\Log::info("Children in serialized: " . count($entity->toArray()['children']));
             } else {
                 \Illuminate\Support\Facades\Log::info("Children MISSING in serialized array");
             }
            }
        } 
        // For Book (if BookChild is Mongo), we should also load manually or check if it works via standard relation
        // Assuming BookChild is also Mongo based on previous checks
        elseif (EntityType::tryFrom($type) === EntityType::BOOK) {
             $children = BookChild::where('book_id', $entity->id)
                ->orderBy('order', 'asc')
                ->get();
             $entity->setRelation('children', $children);
        }
        else {
             // Fallback for standard SQL-SQL
             $entity->load('children');
        }

        return $entity;
    }

    /**
     * Helper to get model class name
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
     * Resolve Parent Entity directly
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
