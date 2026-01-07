<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class VideoSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'video_segments';

    protected $fillable = [
        'video_id',
        'slug',
        'type',
        'title',
        'order',
        'content_blocks',
        'metadata',
        'start_time',
        'end_time',
        'content',
    ];

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }
}
