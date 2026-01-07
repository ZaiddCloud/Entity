<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class AudioSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'audio_segments';

    protected $fillable = [
        'audio_id',
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

    public function audio()
    {
        return $this->belongsTo(Audio::class, 'audio_id', 'id');
    }
}
