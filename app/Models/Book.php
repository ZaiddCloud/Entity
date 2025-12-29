<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use MongoDB\Laravel\Eloquent\HybridRelations;

class Book extends Entity
{
    use HasFactory, HybridRelations;
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
     * العلاقة مع المحتوى في MongoDB (فصول، أجزاء، إلخ)
     */
    public function children()
    {
        return $this->hasMany(BookChild::class, 'book_id', 'id');
    }

    /**
     * العلاقة مع المواضيع (Topics)
     */
    public function topics()
    {
        return $this->belongsToMany(Topic::class, 'book_topic');
    }
}
