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
            'content' => 'required', // JSON or Array
        ]);

        $entity = $this->resolveEntity($type, $slug);
        Gate::authorize('update', $entity);

        // Update the specific content node using the specific model
        $modelClass = $this->getContentModelClass($type);

        $node = $modelClass::where('slug', $slug)->firstOrFail();

        $node->update([
            'content' => $request->content,
            'last_updated' => now(),
            'last_editor_id' => $request->user()->id
        ]);

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
        // هنا يمكن جلب آخر "ContentNode" عمل عليه المستخدم من الجلسة أو قاعدة البيانات
        // للمحاكاة: سنأخذ أول كتاب وأول فصل فيه
        $book = Book::first();
        if (!$book)
            return redirect()->route('dashboard');

        // Resume now searches BookChild
        $node = BookChild::where('book_id', $book->id)->first();

        if (!$node)
            return redirect()->route('dashboard');

        return redirect()->route('studio.show', ['type' => 'book', 'slug' => $node->slug]);
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

        return $entityModel::findOrFail($node->$foreignKey);
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
