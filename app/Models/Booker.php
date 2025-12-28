<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booker extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
    ];

    /**
     * الكتب التي ساهم فيها هذا الشخص
     */
    public function books()
    {
        return $this->morphedByMany(Book::class, 'bookable');
    }

    /**
     * المخطوطات التي ساهم فيها هذا الشخص
     */
    public function manuscripts()
    {
        return $this->morphedByMany(Manuscript::class, 'bookable');
    }

    /**
     * الصوتيات التي ساهم فيها هذا الشخص
     */
    public function audios()
    {
        return $this->morphedByMany(Audio::class, 'bookable');
    }

    /**
     * الفيديوهات التي ساهم فيها هذا الشخص
     */
    public function videos()
    {
        return $this->morphedByMany(Video::class, 'bookable');
    }
}
