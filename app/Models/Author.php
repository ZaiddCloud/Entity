<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'bio',
        'birth_year',
        'death_year',
    ];

    /**
     * الكتب المرتبطة بهذا المؤلف
     */
    public function books()
    {
        return $this->morphedByMany(Book::class, 'authorable');
    }

    /**
     * المرئيات المرتبطة بهذا المؤلف
     */
    public function videos()
    {
        return $this->morphedByMany(Video::class, 'authorable');
    }

    /**
     * الصوتيات المرتبطة بهذا المؤلف
     */
    public function audios()
    {
        return $this->morphedByMany(Audio::class, 'authorable');
    }

    /**
     * المخطوطات المرتبطة بهذا المؤلف
     */
    public function manuscripts()
    {
        return $this->morphedByMany(Manuscript::class, 'authorable');
    }
}
