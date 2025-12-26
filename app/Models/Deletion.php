<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Deletion extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasUuids;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'user_id',
        'reason',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public $timestamps = false;

    /**
     * Get the parent entity model.
     */
    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
