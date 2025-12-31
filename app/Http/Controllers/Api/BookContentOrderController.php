<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Services\BookContentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class BookContentOrderController extends Controller
{
    protected $contentService;

    public function __construct(BookContentService $contentService)
    {
        $this->contentService = $contentService;
    }

    /**
     * Reorder content items.
     * Expected payload: { "items": [{ "id": "...", "order": 1, "parent_id": "..." }, ...] }
     */
    public function update(Request $request, Book $book): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|string',
            'items.*.order' => 'required|integer',
            'items.*.parent_id' => 'nullable|string',
        ]);

        $this->contentService->updateOrder($book, $request->input('items'));

        return response()->json(['message' => 'Order updated successfully']);
    }
}
