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
        'book_id',
        'publisher_id',
        'language_id',
        'shelf_id',
        'file_path',
        'cover_path',
        'format',
        'file_size',
        'isbn',
        'pages',
        'published_year',
        'edition_number',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
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
}
