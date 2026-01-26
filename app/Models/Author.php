<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property string $id
 * @property string $name
 * @property string $slug
 * @property string|null $bio
 * @property int|null $birth_year
 * @property int|null $death_year
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Author extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected static function booted()
    {
        static::creating(function ($author) {
            if (!$author->slug) {
                $author->slug = \App\Helpers\SlugHelper::generate($author->name) . '-' . \Illuminate\Support\Str::random(6);
            }
        });

        static::updating(function ($author) {
            if ($author->isDirty('name') && !$author->slug) {
                $author->slug = \App\Helpers\SlugHelper::generate($author->name) . '-' . \Illuminate\Support\Str::random(6);
            }
        });
    }

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
