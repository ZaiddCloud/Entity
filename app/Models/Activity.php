<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property string $id
 * @property string $entity_id
 * @property string $entity_type
 * @property string $user_id
 * @property string $action
 * @property array|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Database\Eloquent\Model $entity
 */
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

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
