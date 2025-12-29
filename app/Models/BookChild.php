<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BookChild extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'book_children';

    protected $fillable = [
        'book_id',
        'parent_id',
        'type',
        'title',
        'order',
        'language',
        'version',
        'content_blocks',
        'metadata',
        'last_updated',
    ];

    /**
     * Relationship to the Book (MySQL)
     */
    public function book()
    {
        return $this->belongsTo(Book::class, 'book_id', 'id');
    }
}
