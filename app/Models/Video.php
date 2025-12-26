<?php

namespace App\Models;

class Video extends Entity
{
    protected $table = 'videos';

    protected $fillable = [
        'title', 'slug', 'duration', 'format',
        'description', 'cover_path', 'file_path',
        'created_at', 'updated_at'
    ];
}
