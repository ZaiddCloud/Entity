<?php

namespace App\Models;

class Video extends Entity
{
    protected $table = 'videos';

    protected $fillable = [
        'title',
        'slug',
        'duration',
        'format',
        'description',
        'cover_path',
        'file_path',
        'created_at',
        'updated_at'
    ];

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
}
