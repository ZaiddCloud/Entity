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
     */
    public function show(string $type, string $slug): Response
    {
        $entity = $this->resolveEntity($type, $slug);

        // التحقق من الصلاحية
        Gate::authorize('update', $entity);

        // تحضير البيانات عبر الخدمة الموحدة
        // ملاحظة: هنا نحتاج لإيجاد الـ childNode slug الصحيح.
        // إذا كان slug هو slug الكيان الرئيسي، قد نحتاج لأول جزء فيه.
        // ولكن في تصميمنا، الرابط يشير مباشرة لـ ContentNode slug.

        $data = $this->contentService->prepareEditorData($entity, $slug);

        return Inertia::render('Editor/EditorPage', $data);
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

        return redirect()->route('editor.show', ['type' => 'book', 'slug' => $node->slug]);
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
}
