<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class ManuscriptChild extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'manuscript_children';

    protected $fillable = [
        'manuscript_id',
        'slug',
        'type', // e.g., 'page', 'section'
        'title',
        'order',
        'content_blocks', // transcription content
        'metadata', // can hold specific page metadata
        'resource_url', // URL to the specific page image/pdf page if applicable
        'last_updated',
        'is_manually_edited',
        'versions',
    ];

    /**
     * Create a snapshot of current content blocks.
     */
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

    /**
     * Relationship to the Manuscript (MySQL)
     */
    public function manuscript()
    {
        return $this->belongsTo(Manuscript::class, 'manuscript_id', 'id');
    }
}
