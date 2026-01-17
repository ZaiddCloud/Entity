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
     * المسار الموحد للمحرر: /editor/{type}/{slug}
     * Note: Type hint removed to allow RedirectResponse
     */
    public function show(string $type, string $slug)
    {
        // 1. Try to resolve as a specific Content Node (Segment/Page)
        try {
            $entity = $this->resolveEntity($type, $slug);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // 2. If not found, try to resolve as a Parent Entity (Book/Audio/etc)
            $parentEntity = $this->resolveParentEntity($type, $slug);

            if ($parentEntity) {
                // Find the first child node
                $firstChild = $this->contentService->getFirstChild($parentEntity);
                
                if ($firstChild) {
                    return redirect()->route('studio.show', ['type' => $type, 'slug' => $firstChild->slug]);
                }
            }
            // If still not found, throw 404
            abort(404, 'Resource not found');
        }

        // التحقق من الصلاحية
        Gate::authorize('update', $entity);

        // Load siblings for Manuscript if 'code' exists
        if ($type === 'manuscript' && $entity->code) {
             $siblings = Manuscript::where('code', $entity->code)
                ->where('id', '!=', $entity->id)
                ->get();
             $entity->setRelation('siblings', $siblings);
        }

        // Record last active session
        if (auth()->check()) {
            auth()->user()->update([
                'last_studio_type' => $type,
                'last_studio_slug' => $slug
            ]);
        }

        $data = $this->contentService->prepareEditorData($entity, $slug);
        
        // Map to Studio Props
        $studioProps = [
            'type' => $type,
            'entity' => $entity, // Entity now includes siblings if loaded
            'editorContent' => $data['contentNode']->content ?? '',
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
    public function save(Request $request, string $type, string $slug)
    {
        $request->validate([
            'content' => 'required', // Can be string (legacy) or payload array
            'html_content' => 'nullable|string',
            'json_content' => 'nullable|array',
            'plain_text' => 'nullable|string',
        ]);

        $entity = $this->resolveEntity($type, $slug);
        Gate::authorize('update', $entity);

        $modelClass = $this->getContentModelClass($type);
        $node = $modelClass::where('slug', $slug)->firstOrFail();

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
                'slug' => $user->last_studio_slug
            ]);
        }

        // Fallback: Check if we have any recently updated content
        // For simplicity, we fallback to first book child
        $node = BookChild::first();

        if ($node) {
            return redirect()->route('studio.show', ['type' => 'book', 'slug' => $node->slug]);
        }

        return redirect()->route('dashboard');
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

        return $entityModel::with('children')->findOrFail($node->$foreignKey);
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
