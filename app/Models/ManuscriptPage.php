<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

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
