<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $_id
 * @property string $audio_id
 * @property string $slug
 * @property string $type
 * @property string $title
 * @property int $order
 * @property array|null $content_blocks
 * @property array|null $metadata
 * @property float|null $start_time
 * @property float|null $end_time
 * @property string|null $content
 * @property-read \App\Models\Audio $audio
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
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
        'start_time',
        'end_time',
        'duration',
        'file_path',
        'content',
    ];

    public function audio()
    {
        return $this->belongsTo(Audio::class, 'audio_id', 'id');
    }
}
