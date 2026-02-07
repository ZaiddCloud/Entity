<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Audio;
use App\Models\Book;
use App\Models\Manuscript;
use App\Models\Video;
use Illuminate\Http\Request;

class SyncController extends Controller
{
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
        $userId = $request->user()->id;

        // Helper to filter by assignment if needed
        $applyScope = function ($query) use ($scope, $userId) {
            if ($scope === 'assigned') {
                $query->whereHas('assignments', function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                      ->whereIn('status', ['pending', 'in_progress']);
                });
            }
        };

        // Helper to get assigned IDs for manual filtering (if whereHas is too slow or complex)
        // Alternatively, use polymorphic relationship on Assignment model
        // usage: Assignment::where('user_id', $user->id)->where('entity_type', Book::class)->pluck('entity_id')

        $manuscripts = Manuscript::query()
            ->when($scope === 'assigned', function($q) use ($userId) {
                $ids = \App\Models\Assignment::where('user_id', $userId)
                    ->where('entity_type', Manuscript::class)
                    ->active()
                    ->pluck('entity_id');
                $q->whereIn('id', $ids);
            })
            ->with(['children' => function($q) {
                $q->select('id', 'manuscript_id', 'title', 'slug', 'order', 'type');
            }])
            ->get();

        $books = Book::query()
            ->when($scope === 'assigned', function($q) use ($userId) {
                 $ids = \App\Models\Assignment::where('user_id', $userId)
                    ->where('entity_type', Book::class)
                    ->active()
                    ->pluck('entity_id');
                $q->whereIn('id', $ids);
            })
            // Fixed: author is a string, not author_id relation in schema
            ->select('id', 'title', 'slug', 'cover_path', 'author', 'description', 'created_at', 'updated_at')
            ->get();
            
        $audios = Audio::query()
            ->when($scope === 'assigned', function($q) use ($userId) {
                 $ids = \App\Models\Assignment::where('user_id', $userId)
                    ->where('entity_type', Audio::class)
                    ->active()
                    ->pluck('entity_id');
                $q->whereIn('id', $ids);
            })
            ->with('children')
            ->get();

        $videos = Video::query()
            ->when($scope === 'assigned', function($q) use ($userId) {
                 $ids = \App\Models\Assignment::where('user_id', $userId)
                    ->where('entity_type', Video::class)
                    ->active()
                    ->pluck('entity_id');
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
