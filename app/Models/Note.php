<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Note extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasUuids;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'content',
        'user_id',
    ];

    /**
     * Get the parent entity model.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
