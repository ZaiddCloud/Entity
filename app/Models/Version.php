<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Version extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'versionable_id',
        'versionable_type',
        'publisher_id',
        'language_id',
        'shelf_id',
        'title',
        'file_path',
        'cover_path',
        'format',
        'file_size',
        'isbn',
        'pages',
        'published_year',
        'edition_number',
    ];

    /**
     * العنصر المرتبط بهذه النسخة (كتاب، فيديو، إلخ)
     */
    public function versionable()
    {
        return $this->morphTo();
    }

    public function publisher()
    {
        return $this->belongsTo(Publisher::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }
}
