<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class Shelf extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'location_code',
        'capacity',
    ];

    public function versions()
    {
        return $this->hasMany(Version::class);
    }
}
