<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Video;
use App\Services\EntityQueryService;
use Illuminate\Http\Request;

class SyncController extends Controller
{
    public function __construct(
        protected EntityQueryService $queryService
    ) {}

    /**
     * Download all user entities for offline usage.
     * Returns a lightweight manifest of all content.
     */
    public function index(Request $request)
    {
        // Increase memory limit for this heavy operation if needed
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        $scope = $request->query('scope', 'full'); // full, assigned
        $user = $request->user();

        $manuscripts = Manuscript::query()
            ->when($scope === 'assigned', function($q) use ($user) {
                $ids = $this->queryService->getAssignedEntityIds($user, Manuscript::class);
                $q->whereIn('id', $ids);
            })
            ->with(['children' => function($q) {
                $q->select('id', 'manuscript_id', 'title', 'slug', 'order', 'type');
            }])
            ->get();

        $books = Book::query()
            ->when($scope === 'assigned', function($q) use ($user) {
                $ids = $this->queryService->getAssignedEntityIds($user, Book::class);
                $q->whereIn('id', $ids);
            })
            ->select('id', 'title', 'slug', 'cover_path', 'author', 'description', 'created_at', 'updated_at')
            ->get();
            
        $audios = Audio::query()
            ->when($scope === 'assigned', function($q) use ($user) {
                $ids = $this->queryService->getAssignedEntityIds($user, Audio::class);
                $q->whereIn('id', $ids);
            })
            ->with('children')
            ->get();

        $videos = Video::query()
            ->when($scope === 'assigned', function($q) use ($user) {
                $ids = $this->queryService->getAssignedEntityIds($user, Video::class);
                $q->whereIn('id', $ids);
            })
            ->with('children')
            ->get();

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'scope' => $scope,
            'entities' => [
                'manuscripts' => $manuscripts,
                'books' => $books,
                'audios' => $audios,
                'videos' => $videos,
            ]
        ]);
    }
}
