<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookChild;
use App\Services\BookContentService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BookContentController extends Controller
{
    protected $contentService;

    public function __construct(BookContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * عارض الكتاب الرئيسي
     */
    public function show(Book $book, $childId = null)
    {
        $initialContent = null;
        if ($childId) {
            $child = BookChild::where('book_id', $book->id)->where('_id', $childId)->first();
            if ($child) {
                $initialContent = [
                    'id' => $child->_id,
                    'title' => $child->title,
                    'type' => $child->type,
                    'content_blocks' => $child->content_blocks ?? [],
                    'metadata' => $child->metadata ?? []
                ];
            }
        }

        return Inertia::render('Books/Reader/Index', [
            'book' => $book->only(['id', 'title', 'slug', 'author']),
            'initialHierarchy' => $this->contentService->getHierarchy($book),
            'initialContent' => $initialContent,
            'childId' => $childId
        ]);
    }

    /**
     * جلب محتويات وحدة معينة (فصل، مسألة، إلخ)
     */
    public function getChildContent(BookChild $child)
    {
        return response()->json([
            'content_blocks' => $child->content_blocks ?? [],
            'title' => $child->title,
            'type' => $child->type,
            'metadata' => $child->metadata ?? []
        ]);
    }
}
