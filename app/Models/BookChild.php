<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $_id
 * @property string $book_id
 * @property string|null $parent_id
 * @property string $slug
 * @property string $type
 * @property string $title
 * @property int $order
 * @property string|null $language
 * @property string|null $version
 * @property array $content_blocks
 * @property array|null $metadata
 * @property string|null $last_updated
 * @property bool|null $is_manually_edited
 * @property array|null $versions
 * @property string|null $content
 * @property-read \App\Models\Book $book
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class BookChild extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'book_children';

    protected $fillable = [
        'book_id',
        'parent_id',
        'slug',
        'type',
        'title',
        'order',
        'language',
        'version',
        'content_blocks',
        'metadata',
        'last_updated',
        'is_manually_edited',
        'versions',
        'content',
    ];

    /**
     * Create a snapshot of current content blocks.
     */
    public function createVersion($description = 'Manual Edit')
    {
        $versions = $this->versions ?? [];
        $versions[] = [
            'content_blocks' => $this->content_blocks,
            'created_at' => now()->toISOString(),
            'description' => $description,
        ];
        $this->versions = $versions;
        $this->save();
    }

    /**
     * Relationship to the Book (MySQL)
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
