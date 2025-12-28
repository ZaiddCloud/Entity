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
     * الكتب التي ألفها هذا المؤلف
     */
    public function books()
    {
        return $this->belongsToMany(Book::class, 'author_book');
    }
}
