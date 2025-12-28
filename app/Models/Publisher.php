<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publisher extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'country_code',
        'logo_path',
    ];

    /**
     * النسخ التي نشرها هذا الناشر
     */
    public function versions()
    {
        return $this->hasMany(Version::class);
    }
}
