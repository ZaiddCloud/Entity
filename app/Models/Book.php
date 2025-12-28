<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Book extends Entity
{
    use HasFactory;
    protected $table = 'books';

    protected $fillable = [
        'title',
        'slug',
        'author',
        'isbn',
        'description',
        'cover_path',
        'file_path',
        'created_at',
        'updated_at'
    ];

    /**
     * الخصائص الخاصة بالكتاب
     */
    public function getDisplayNameAttribute(): string
    {
        return "{$this->title} - {$this->author}";
    }

    /**
     * العلاقة مع النسخ (Versions)
     */
    public function versions()
    {
        return $this->hasMany(Version::class);
    }

    /**
     * العلاقة مع المؤلفين (Authors)
     */
    public function authors()
    {
        return $this->belongsToMany(Author::class, 'author_book');
    }

    /**
     * العلاقة مع المساهمين (Bookers - Polymorphic)
     */
    public function bookers()
    {
        return $this->morphToMany(Booker::class, 'bookable');
    }

    /**
     * العلاقة مع المواضيع (Topics)
     */
    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'book_topic');
    }
}
