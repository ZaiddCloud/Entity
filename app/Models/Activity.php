<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'activity_type',
        'description',
        'user_id',
        'entity_id',
        'entity_type',
        'changes'
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
