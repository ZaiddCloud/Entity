<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

/**
 * @property string $_id
 * @property string $manuscript_id
 * @property string $slug
 * @property string $type
 * @property string $title
 * @property int $order
 * @property array|null $content_blocks
 * @property array|null $metadata
 * @property string|null $last_updated
 * @property string|null $folio_number
 * @property string|null $image_url
 * @property string|null $transcription_status
 * @property string|null $content
 * @property array|null $versions
 * @property-read \App\Models\Manuscript $manuscript
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ManuscriptPage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'manuscript_pages';

    protected $fillable = [
        'manuscript_id',
        'slug',
        'type',
        'title',
        'order',
        'content_blocks',
        'metadata',
        'last_updated',
        'folio_number',
        'image_url',
        'transcription_status',
        'content',
        'json_content',
        'plain_text',
    ];

    public function manuscript()
    {
        return $this->belongsTo(Manuscript::class, 'manuscript_id', 'id');
    }

    public function createVersion($description = 'Manual Edit')
    {
        $versions = $this->versions ?? [];
        $versions[] = [
            'content_blocks' => $this->content_blocks,
            'created_at' => now()->toISOString(),
            'description' => $description,
        ];
        $this->versions = $versions;
        $this->save();
    }
}
