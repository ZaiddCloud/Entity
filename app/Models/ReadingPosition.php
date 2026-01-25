<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReadingPosition extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'entity_type',
        'entity_id',
        'node_slug',
        'scroll_offset',
        'timestamp',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function entity()
    {
        return $this->morphTo();
    }
}
