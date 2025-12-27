<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasUuids;

    protected $fillable = [
        'entity_id',
        'entity_type',
        'content',
        'user_id',
        'parent_id',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
