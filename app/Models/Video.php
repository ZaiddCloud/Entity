<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\HybridRelations;

class Video extends Entity
{
    use HybridRelations;
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
     * العلاقة مع مشاهد الفيديو في MongoDB
     */
    public function children()
    {
        return $this->hasMany(VideoSegment::class, 'video_id', 'id');
    }
}
