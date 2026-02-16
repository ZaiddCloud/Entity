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
 * @property bool|null $is_manually_edited
 * @property string|null $resource_url
 * @property string|null $parent_id
 * @property array|null $versions
 * @property-read \App\Models\Manuscript $manuscript
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class ManuscriptPage extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'manuscript_pages';
    protected $primaryKey = '_id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $guarded = [];

    public function manuscript()
    {
        return $this->belongsTo(Manuscript::class, 'manuscript_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(ManuscriptPage::class, 'parent_id', '_id');
    }

    public function parent()
    {
        return $this->belongsTo(ManuscriptPage::class, 'parent_id', '_id');
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
