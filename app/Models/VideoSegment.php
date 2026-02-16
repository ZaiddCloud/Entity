<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $_id
 * @property string $video_id
 * @property string $slug
 * @property string $type
 * @property string $title
 * @property int $order
 * @property array|null $content_blocks
 * @property array|null $metadata
 * @property float|null $start_time
 * @property float|null $end_time
 * @property string|null $content
 * @property-read \App\Models\Video $video
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class VideoSegment extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'video_segments';

    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $guarded = [];

    public function video()
    {
        return $this->belongsTo(Video::class, 'video_id', 'id');
    }
}
