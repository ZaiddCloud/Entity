<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Entity
{
    use HasFactory;
    protected $table = 'books';

    protected $fillable = [
        'title', 'slug', 'author', 'isbn',
        'description', 'cover_path', 'file_path',
        'created_at', 'updated_at'
    ];

    /**
     * الخصائص الخاصة بالكتاب
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->title} - {$this->author}";
    }
}
