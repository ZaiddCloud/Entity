<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $entity_id
 * @property string $entity_type
 * @property string $user_id
 * @property string $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 */
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
