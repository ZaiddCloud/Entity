<?php

namespace App\Models;

class Video extends Entity
{
    protected $table = 'videos';

    protected $fillable = [
        'title', 'slug', 'duration', 'format',
        'created_at', 'updated_at'
    ];
}
