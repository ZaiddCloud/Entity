<?php

namespace App\Services;

use App\Models\Book;
use App\Models\BookChild;
use Illuminate\Support\Collection;

class BookContentService
{
    public function addChild(Book $book, array $data): BookChild
    {
        return BookChild::create([
            'book_id' => $book->id,
            'parent_id' => $data['parent_id'] ?? null,
            'type' => $data['type'] ?? 'chapter',
            'title' => $data['title'],
            'slug' => $data['slug'] ?? \Illuminate\Support\Str::slug($data['title']) . '-' . uniqid(),
            'order' => $data['order'] ?? 0,
            'content' => $data['content'] ?? null,
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

        if (isset($block['body'])) {
            $blocks[] = [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $block['body']
                    ]
                ]
            ];

            // Append to HTML content field for Editor compatibility
            $currentContent = $child->content ?? '';
            $child->content = $currentContent . '<p>' . htmlspecialchars($block['body']) . '</p>';
        } else {
            $blocks[] = $block;
        }

        $child->update(['content_blocks' => $blocks, 'last_updated' => now()]);

        return $child;
    }
    /**
     * Batch update order of content items.
     */
    public function updateOrder(Book $book, array $items): void
    {
        foreach ($items as $item) {
            BookChild::where('book_id', $book->id)
                ->where('_id', $item['id'])
                ->update([
                    'order' => $item['order'],
                    'parent_id' => $item['parent_id'] ?? null
                ]);
        }
    }
}
