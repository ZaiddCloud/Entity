<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookChild;
use Illuminate\Support\Collection;

class BookContentService
{
    /**
     * Create a new child unit for a book.
     * 
     * @param Book $book
     * @param array $data
     * @return BookChild
     */
    public function addChild(Book $book, array $data): BookChild
    {
        return BookChild::create([
            'book_id' => $book->id,
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'] ?? 'chapter',
            'title' => $data['title'],
            'order' => $data['order'] ?? 0,
            'content_blocks' => $data['content_blocks'] ?? [],
            'metadata' => $data['metadata'] ?? [],
            'last_updated' => now(),
        ]);
    }

    /**
     * Get the full hierarchy of a book (Titles only for sidebar).
     */
    public function getHierarchy(Book $book): Collection
    {
        return BookChild::where('book_id', $book->id)
            ->orderBy('order')
            ->get(['_id', 'parent_id', 'type', 'title', 'order']);
    }

    /**
     * Add a block to a specific child unit.
     */
    public function addBlock(BookChild $child, array $block): BookChild
    {
        $blocks = $child->content_blocks ?? [];
        $blocks[] = array_merge([
            'id' => uniqid('b_'),
            'type' => 'paragraph',
            'body' => '',
            'annotations' => []
        ], $block);

        $child->update(['content_blocks' => $blocks, 'last_updated' => now()]);
        
        return $child;
    }
}
