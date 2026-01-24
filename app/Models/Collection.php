<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

/**
 * @property string $id
 * @property string $name
 * @property string $user_id
 * @property string|null $description
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @property-read \Illuminate\Support\Collection $entities
 */
class Collection extends Model
{
    use \App\Traits\HasPolymorphicRelations;
    use \Illuminate\Database\Eloquent\Factories\HasFactory;
    use HasUuids, \App\Traits\TaxonomyRelationships;

    protected function getTaxonomyPivotTable(): string
    {
        return 'collectables';
    }

    protected $fillable = ['name', 'user_id', 'description', 'is_public'];

    /**
     * Get the user that owns the collection.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Accessor للـ Entities
     */
    public function getEntitiesAttribute()
    {
        return $this->getEntitiesFromCollections();
    }

    /**
     * إضافة entity مع order_column
     */
    public function addEntity($entity)
    {
        $currentMax = $this->entities->max(function ($entity) {
            return $entity->pivot_data['order_column'] ?? 0;
        }) ?? 0;

        return $this->attachToEntity($entity, 'collections', [
            'order_column' => $currentMax + 1,
            'added_at' => now()
        ]);
    }
}
