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
     * العلاقة مع النسخ (Versions - Polymorphic)
     */
    public function versions()
    {
        return $this->morphMany(Version::class, 'versionable');
    }

    /**
     * العلاقة مع المؤلفين (Authors - Polymorphic)
     */
    public function authors()
    {
        return $this->morphToMany(Author::class, 'authorable');
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
