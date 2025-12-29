<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class BookChild extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'book_children';

    protected $fillable = [
        'book_id',
        'language',
        'version',
        'chapters', // This will store the hierarchical JSON structure
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
